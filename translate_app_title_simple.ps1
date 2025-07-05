# PowerShell script to translate app_title in language files
# This script will replace 'Renal Tales' with appropriate translations

# Define translations for "Renal Tales" in different languages
$translations = @{
    'af' = 'Nierverhalende'
    'am' = 'Renal Tales'  # Keep as English for now due to script encoding
    'ar' = 'Renal Tales'  # Keep as English for now due to script encoding
    'as' = 'Renal Tales'  # Keep as English for now due to script encoding
    'ay' = 'Riñon Aruskipäwinaka'
    'az' = 'Böyrək Hekayələri'
    'bcl' = 'Mga Kwentong Bato'
    'be' = 'Renal Tales'  # Keep as English for now due to script encoding
    'bg' = 'Renal Tales'  # Keep as English for now due to script encoding
    'bh' = 'Renal Tales'  # Keep as English for now due to script encoding
    'bho' = 'Renal Tales'  # Keep as English for now due to script encoding
    'bm' = 'Bamanankan Koron'
    'bn' = 'Renal Tales'  # Keep as English for now due to script encoding
    'bo' = 'Renal Tales'  # Keep as English for now due to script encoding
    'ca' = 'Contes Renals'
    'ceb' = 'Mga Istorya sa Bato'
    'cs' = 'Ledvinové Příběhy'
    'cy' = 'Chwedlau Arennau'
    'da' = 'Nyrefortællinger'
    'de' = 'Nierengeschichten'
    'dv' = 'Renal Tales'  # Keep as English for now due to script encoding
    'el' = 'Renal Tales'  # Keep as English for now due to script encoding
    'en' = 'Renal Tales'
    'eo' = 'Renaj Rakontoj'
    'es' = 'Cuentos Renales'
    'et' = 'Neerulood'
    'eu' = 'Giltzurrun Ipuinak'
    'fa' = 'Renal Tales'  # Keep as English for now due to script encoding
    'ff' = 'Taali Kollaaji'
    'fi' = 'Munuaistarinoita'
    'fo' = 'Nýrnasogor'
    'fr' = 'Contes Rénaux'
    'ga' = 'Scéalta Duáin'
    'gd' = 'Sgeulachdan Àrainneach'
    'gl' = 'Contos Renais'
    'gn' = 'Apytuũ Moñeẽ'
    'gu' = 'Renal Tales'  # Keep as English for now due to script encoding
    'ha' = 'Labarun Koda'
    'he' = 'Renal Tales'  # Keep as English for now due to script encoding
    'hi' = 'Renal Tales'  # Keep as English for now due to script encoding
    'hil' = 'Mga Sugilanon sa Bato'
    'hr' = 'Bubrežne Priče'
    'ht' = 'Istwa Ren yo'
    'hu' = 'Vesetörténetek'
    'hy' = 'Renal Tales'  # Keep as English for now due to script encoding
    'id' = 'Kisah Ginjal'
    'ig' = 'Akụkọ Akụrụ'
    'ilo' = 'Dagiti Estoria ti Bekkel'
    'is' = 'Nýrnasögur'
    'it' = 'Racconti Renali'
    'ja' = 'Renal Tales'  # Keep as English for now due to script encoding
    'jv' = 'Crita Ginjel'
    'ka' = 'Renal Tales'  # Keep as English for now due to script encoding
    'kg' = 'Makanda ma Mpiku'
    'kk' = 'Renal Tales'  # Keep as English for now due to script encoding
    'kl' = 'Tarnip Oqaluttuaatit'
    'km' = 'Renal Tales'  # Keep as English for now due to script encoding
    'kn' = 'Renal Tales'  # Keep as English for now due to script encoding
    'ko' = 'Renal Tales'  # Keep as English for now due to script encoding
    'ky' = 'Renal Tales'  # Keep as English for now due to script encoding
    'lb' = 'Nierengeschichten'
    'lg' = 'Emboozi z Ensigo'
    'ln' = 'Lisolo ya Mpiku'
    'lo' = 'Renal Tales'  # Keep as English for now due to script encoding
    'lt' = 'Inkstų Pasakos'
    'lua' = 'Maluangu a Mpiku'
    'lv' = 'Nieru Stāsti'
    'mai' = 'Renal Tales'  # Keep as English for now due to script encoding
    'mg' = 'Tantara Voa'
    'mk' = 'Renal Tales'  # Keep as English for now due to script encoding
    'ml' = 'Renal Tales'  # Keep as English for now due to script encoding
    'mn' = 'Renal Tales'  # Keep as English for now due to script encoding
    'mr' = 'Renal Tales'  # Keep as English for now due to script encoding
    'ms' = 'Kisah Buah Pinggang'
    'mt' = 'Stejjer tar-Rieni'
    'my' = 'Renal Tales'  # Keep as English for now due to script encoding
    'nd' = 'Izindaba Zezinso'
    'ne' = 'Renal Tales'  # Keep as English for now due to script encoding
    'nl' = 'Nierverhalen'
    'no' = 'Nyrefortellinger'
    'nr' = 'Izindaba Zezinso'
    'nso' = 'Dipale tša Dikoloto'
    'ny' = 'Nkhani za Impsyo'
    'om' = 'Seenaa Kalee'
    'or' = 'Renal Tales'  # Keep as English for now due to script encoding
    'pa' = 'Renal Tales'  # Keep as English for now due to script encoding
    'pam' = 'Kwentung Batu'
    'pl' = 'Opowieści Nerkowe'
    'ps' = 'Renal Tales'  # Keep as English for now due to script encoding
    'pt' = 'Contos Renais'
    'qu' = 'Riñon Willakuykuna'
    'rm' = 'Narrativas Renals'
    'rn' = 'Inkuru z Impyiko'
    'ro' = 'Poveștile Renale'
    'ru' = 'Renal Tales'  # Keep as English for now due to script encoding
    'rw' = 'Inkuru z Impyiko'
    'sa' = 'Renal Tales'  # Keep as English for now due to script encoding
    'sd' = 'Renal Tales'  # Keep as English for now due to script encoding
    'se' = 'Báhppat'
    'sg' = 'Tî Mbëtï'
    'si' = 'Renal Tales'  # Keep as English for now due to script encoding
    'sk' = 'Obličkové Príbehy'
    'sl' = 'Ledvične Zgodbe'
    'sn' = 'Ngano dzeItsvo'
    'so' = 'Sheekooyin Kelli'
    'sq' = 'Përrallat e Veshkave'
    'sr' = 'Renal Tales'  # Keep as English for now due to script encoding
    'ss' = 'Tindzaba Tetikoloto'
    'st' = 'Dipale tsa Dilakelo'
    'su' = 'Dongéng Ginjal'
    'sv' = 'Njurberättelser'
    'sw' = 'Hadithi za Figo'
    'ta' = 'Renal Tales'  # Keep as English for now due to script encoding
    'te' = 'Renal Tales'  # Keep as English for now due to script encoding
    'tg' = 'Renal Tales'  # Keep as English for now due to script encoding
    'th' = 'Renal Tales'  # Keep as English for now due to script encoding
    'ti' = 'Renal Tales'  # Keep as English for now due to script encoding
    'tk' = 'Böwrek Ertekleri'
    'tl' = 'Mga Kwentong Bato'
    'tn' = 'Dikgang tsa Dikoloto'
    'tr' = 'Böbrek Hikayeleri'
    'ts' = 'Mitsheketo ya Swikoloto'
    'ug' = 'Renal Tales'  # Keep as English for now due to script encoding
    'uk' = 'Renal Tales'  # Keep as English for now due to script encoding
    'ur' = 'Renal Tales'  # Keep as English for now due to script encoding
    'uz' = 'Buyrak Hikoyalari'
    've' = 'Dzingano dza Tshikoloto'
    'vi' = 'Truyện Thận'
    'war' = 'Mga Istorya han Bato'
    'wo' = 'Leeb yu Reer'
    'wuu' = 'Renal Tales'  # Keep as English for now due to script encoding
    'xh' = 'Iingxelo Zezintso'
    'yo' = 'Àwọn Ìtàn Kíndìnrín'
    'yue' = 'Renal Tales'  # Keep as English for now due to script encoding
    'zh' = 'Renal Tales'  # Keep as English for now due to script encoding
    'zu' = 'Izindaba Zezinso'
}

# Get all PHP files in the languages directory
$languageFiles = Get-ChildItem -Path "languages" -Filter "*.php"

$updatedFiles = @()
$alreadyTranslated = @()
$notFound = @()

foreach ($file in $languageFiles) {
    $languageCode = [System.IO.Path]::GetFileNameWithoutExtension($file.Name)
    
    # Read the file content
    $content = Get-Content $file.FullName -Raw -Encoding UTF8
    
    # Check if it currently has 'Renal Tales' in app_title
    if ($content -match "'app_title'\s*=>\s*'Renal Tales'") {
        # Check if we have a translation for this language
        if ($translations.ContainsKey($languageCode)) {
            $translation = $translations[$languageCode]
            
            # Replace 'Renal Tales' with the translation
            $newContent = $content -replace "'app_title'\s*=>\s*'Renal Tales'", "'app_title' => '$translation'"
            
            # Write back to file
            Set-Content -Path $file.FullName -Value $newContent -NoNewline -Encoding UTF8
            
            $updatedFiles += "$languageCode -> $translation"
            Write-Host "Updated $languageCode.php: 'Renal Tales' -> '$translation'" -ForegroundColor Green
        } else {
            $notFound += $languageCode
            Write-Host "Translation not found for language: $languageCode" -ForegroundColor Yellow
        }
    } else {
        # Check if it already has a different translation
        if ($content -match "'app_title'\s*=>\s*'([^']+)'") {
            $currentTranslation = $matches[1]
            if ($currentTranslation -ne 'Renal Tales') {
                $alreadyTranslated += "$languageCode -> $currentTranslation"
                Write-Host "Already translated $languageCode.php: '$currentTranslation'" -ForegroundColor Cyan
            }
        }
    }
}

# Summary report
Write-Host "`n=== TRANSLATION SUMMARY ===" -ForegroundColor Magenta
Write-Host "Updated files: $($updatedFiles.Count)" -ForegroundColor Green
foreach ($update in $updatedFiles) {
    Write-Host "  $update" -ForegroundColor Green
}

Write-Host "`nAlready translated files: $($alreadyTranslated.Count)" -ForegroundColor Cyan
foreach ($existing in $alreadyTranslated) {
    Write-Host "  $existing" -ForegroundColor Cyan
}

Write-Host "`nLanguages without translations: $($notFound.Count)" -ForegroundColor Yellow
foreach ($missing in $notFound) {
    Write-Host "  $missing" -ForegroundColor Yellow
}

Write-Host "`nTranslation process completed!" -ForegroundColor Magenta
