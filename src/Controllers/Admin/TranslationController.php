<?php

declare(strict_types=1);

namespace RenalTales\Controllers\Admin;

use RenalTales\Services\TranslationService;
use RenalTales\Models\Language;

/**
 * Translation Admin Controller
 * 
 * Handles admin interface for translation management
 */
class TranslationController
{
    private TranslationService $translationService;
    private Language $languageModel;

    public function __construct()
    {
        $this->translationService = new TranslationService();
        $this->languageModel = new Language();
    }

    /**
     * Display translation management dashboard
     */
    public function index(): void
    {
        $data = [
            'languages' => $this->languageModel->getActiveLanguages(),
            'statistics' => $this->translationService->getLanguageStatistics(),
            'groups' => $this->translationService->getTranslationGroups(),
            'cache_stats' => $this->translationService->getCacheStatistics()
        ];

        $this->render('admin/translations/index', $data);
    }

    /**
     * Display translations for a specific language
     */
    public function show(): void
    {
        $languageCode = $_GET['lang'] ?? 'en';
        $group = $_GET['group'] ?? null;
        $search = $_GET['search'] ?? '';

        $translations = [];
        if ($search) {
            $translations = $this->translationService->searchTranslations($search, $languageCode);
        } else {
            $translations = $this->translationService->getAllTranslations($group);
        }

        $data = [
            'language_code' => $languageCode,
            'language' => $this->languageModel->getLanguageByCode($languageCode),
            'translations' => $translations,
            'groups' => $this->translationService->getTranslationGroups(),
            'selected_group' => $group,
            'search' => $search
        ];

        $this->render('admin/translations/show', $data);
    }

    /**
     * Create or update translation
     */
    public function save(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
            return;
        }

        $key = $_POST['key'] ?? '';
        $text = $_POST['text'] ?? '';
        $group = $_POST['group'] ?? 'default';
        $languageCode = $_POST['language'] ?? 'en';

        if (empty($key) || empty($text)) {
            $this->jsonResponse(['error' => 'Key and text are required'], 400);
            return;
        }

        $success = $this->translationService->saveTranslation($key, $text, $group, $languageCode);

        if ($success) {
            $this->jsonResponse(['success' => true, 'message' => 'Translation saved successfully']);
        } else {
            $this->jsonResponse(['error' => 'Failed to save translation'], 500);
        }
    }

    /**
     * Delete translation
     */
    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
            return;
        }

        $key = $_POST['key'] ?? '';
        $group = $_POST['group'] ?? 'default';
        $languageCode = $_POST['language'] ?? 'en';

        if (empty($key)) {
            $this->jsonResponse(['error' => 'Key is required'], 400);
            return;
        }

        $success = $this->translationService->deleteTranslation($key, $group, $languageCode);

        if ($success) {
            $this->jsonResponse(['success' => true, 'message' => 'Translation deleted successfully']);
        } else {
            $this->jsonResponse(['error' => 'Failed to delete translation'], 500);
        }
    }

    /**
     * Import translations from file
     */
    public function import(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
            return;
        }

        $languageCode = $_POST['language'] ?? 'en';
        
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $this->jsonResponse(['error' => 'No file uploaded'], 400);
            return;
        }

        $file = $_FILES['file'];
        $fileContent = file_get_contents($file['tmp_name']);
        
        // Parse different file formats
        $translations = [];
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        
        switch ($fileExtension) {
            case 'json':
                $translations = json_decode($fileContent, true);
                break;
            case 'php':
                $translations = eval('return ' . $fileContent . ';');
                break;
            case 'csv':
                $translations = $this->parseCSV($fileContent);
                break;
            default:
                $this->jsonResponse(['error' => 'Unsupported file format'], 400);
                return;
        }

        if (empty($translations)) {
            $this->jsonResponse(['error' => 'No translations found in file'], 400);
            return;
        }

        $success = $this->translationService->importTranslations($translations, $languageCode);

        if ($success) {
            $this->jsonResponse(['success' => true, 'message' => 'Translations imported successfully']);
        } else {
            $this->jsonResponse(['error' => 'Failed to import translations'], 500);
        }
    }

    /**
     * Export translations to file
     */
    public function export(): void
    {
        $languageCode = $_GET['language'] ?? 'en';
        $group = $_GET['group'] ?? null;
        $format = $_GET['format'] ?? 'json';

        $translations = $this->translationService->exportTranslations($languageCode, $group);

        $filename = "translations_{$languageCode}";
        if ($group) {
            $filename .= "_{$group}";
        }

        switch ($format) {
            case 'json':
                $this->downloadFile($filename . '.json', json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                break;
            case 'php':
                $this->downloadFile($filename . '.php', "<?php\n\nreturn " . var_export($translations, true) . ";\n");
                break;
            case 'csv':
                $this->downloadFile($filename . '.csv', $this->generateCSV($translations));
                break;
            default:
                $this->jsonResponse(['error' => 'Unsupported format'], 400);
        }
    }

    /**
     * Manage languages
     */
    public function languages(): void
    {
        $data = [
            'languages' => $this->languageModel->getLanguageStatistics(),
            'supported_languages' => $this->translationService->getSupportedLanguages()
        ];

        $this->render('admin/translations/languages', $data);
    }

    /**
     * Add new language
     */
    public function addLanguage(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
            return;
        }

        $data = [
            'code' => $_POST['code'] ?? '',
            'name' => $_POST['name'] ?? '',
            'native_name' => $_POST['native_name'] ?? '',
            'flag_icon' => $_POST['flag_icon'] ?? '',
            'direction' => $_POST['direction'] ?? 'ltr',
            'is_active' => isset($_POST['is_active']),
            'sort_order' => (int) ($_POST['sort_order'] ?? 0)
        ];

        if (empty($data['code']) || empty($data['name']) || empty($data['native_name'])) {
            $this->jsonResponse(['error' => 'Code, name, and native name are required'], 400);
            return;
        }

        if ($this->languageModel->languageExists($data['code'])) {
            $this->jsonResponse(['error' => 'Language code already exists'], 400);
            return;
        }

        $success = $this->languageModel->addLanguage($data);

        if ($success) {
            $this->translationService->refreshSupportedLanguages();
            $this->jsonResponse(['success' => true, 'message' => 'Language added successfully']);
        } else {
            $this->jsonResponse(['error' => 'Failed to add language'], 500);
        }
    }

    /**
     * Clear cache
     */
    public function clearCache(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
            return;
        }

        $languageCode = $_POST['language'] ?? null;

        if ($languageCode) {
            $success = $this->translationService->clearCache();
        } else {
            $success = $this->translationService->clearAllCache();
        }

        if ($success) {
            $this->jsonResponse(['success' => true, 'message' => 'Cache cleared successfully']);
        } else {
            $this->jsonResponse(['error' => 'Failed to clear cache'], 500);
        }
    }

    /**
     * Warm up cache
     */
    public function warmupCache(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
            return;
        }

        $success = $this->translationService->warmUpCache();

        if ($success) {
            $this->jsonResponse(['success' => true, 'message' => 'Cache warmed up successfully']);
        } else {
            $this->jsonResponse(['error' => 'Failed to warm up cache'], 500);
        }
    }

    /**
     * Parse CSV content
     */
    private function parseCSV(string $content): array
    {
        $lines = explode("\n", $content);
        $translations = [];
        
        foreach ($lines as $line) {
            if (trim($line) === '') continue;
            
            $data = str_getcsv($line);
            if (count($data) >= 3) {
                $group = $data[0];
                $key = $data[1];
                $text = $data[2];
                
                if ($group === 'default') {
                    $translations[$key] = $text;
                } else {
                    $translations[$group][$key] = $text;
                }
            }
        }
        
        return $translations;
    }

    /**
     * Generate CSV content
     */
    private function generateCSV(array $translations): string
    {
        $csv = "Group,Key,Translation\n";
        
        foreach ($translations as $group => $items) {
            if (is_array($items)) {
                foreach ($items as $key => $text) {
                    $csv .= '"' . str_replace('"', '""', $group) . '","' . 
                           str_replace('"', '""', $key) . '","' . 
                           str_replace('"', '""', $text) . '"' . "\n";
                }
            } else {
                $csv .= '"default","' . str_replace('"', '""', $group) . '","' . 
                       str_replace('"', '""', $items) . '"' . "\n";
            }
        }
        
        return $csv;
    }

    /**
     * Download file
     */
    private function downloadFile(string $filename, string $content): void
    {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($content));
        
        echo $content;
        exit;
    }

    /**
     * Send JSON response
     */
    private function jsonResponse(array $data, int $status = 200): void
    {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
        exit;
    }

    /**
     * Render view
     */
    private function render(string $view, array $data = []): void
    {
        extract($data);
        
        $viewPath = __DIR__ . '/../../../resources/views/' . $view . '.php';
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            echo "View not found: $view";
        }
    }
}
