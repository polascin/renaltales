# PowerShell script to translate app_title in language files
# This script will replace 'Renal Tales' with appropriate translations

# Define translations for "Renal Tales" in different languages
$translations = @{
    'af' = 'Nierverhalende'           # Afrikaans
    'am' = 'የኩላሊት ታሪኮች'              # Amharic
    'ar' = 'حكايات كلوية'             # Arabic
    'as' = 'বৃক্কৰ কাহিনী'             # Assamese
    'ay' = 'Riñon Aruskipäwinaka'     # Aymara
    'az' = 'Böyrək Hekayələri'        # Azerbaijani
    'bcl' = 'Mga Kwentong Bato'       # Bikol Central
    'be' = 'Ныркавыя Апавяданні'      # Belarusian
    'bg' = 'Бъбречни Приказки'        # Bulgarian
    'bh' = 'गुर्दा की कहानियां'        # Bhojpuri
    'bho' = 'गुर्दा की कहानियां'       # Bhojpuri
    'bm' = 'Bamanankan Kɔrɔn'        # Bambara
    'bn' = 'বৃক্কের কাহিনী'            # Bengali
    'bo' = 'མཁལ་མའི་གཏམ་རྒྱུད།'        # Tibetan
    'ca' = 'Contes Renals'           # Catalan
    'ceb' = 'Mga Istorya sa Bato'    # Cebuano
    'cs' = 'Ledvinové Příběhy'       # Czech
    'cy' = 'Chwedlau Arennau'        # Welsh
    'da' = 'Nyrefortællinger'        # Danish
    'de' = 'Nierengeschichten'       # German
    'dv' = 'ގުރުދާގެ ހަދީސް'           # Dhivehi
    'el' = 'Νεφρικές Ιστορίες'       # Greek
    'en' = 'Renal Tales'             # English
    'eo' = 'Renaj Rakontoj'          # Esperanto
    'es' = 'Cuentos Renales'         # Spanish
    'et' = 'Neerulood'               # Estonian
    'eu' = 'Giltzurrun Ipuinak'      # Basque
    'fa' = 'داستان‌های کلیوی'          # Persian
    'ff' = 'Taali Kollaaji'          # Fulfulde
    'fi' = 'Munuaistarinoita'        # Finnish
    'fo' = 'Nýrnasogor'             # Faroese
    'fr' = 'Contes Rénaux'          # French
    'ga' = 'Scéalta Duáin'          # Irish
    'gd' = 'Sgeulachdan Àrainneach'  # Scottish Gaelic
    'gl' = 'Contos Renais'          # Galician
    'gn' = 'Apytu''ũ Moñe''ẽ'        # Guarani
    'gu' = 'મૂત્રપિંડની વાર્તાઓ'        # Gujarati
    'ha' = 'Labarun Koda'            # Hausa
    'he' = 'סיפורי כליות'            # Hebrew
    'hi' = 'गुर्दे की कहानियां'        # Hindi
    'hil' = 'Mga Sugilanon sa Bato'  # Hiligaynon
    'hr' = 'Bubrežne Priče'          # Croatian
    'ht' = 'Istwa Ren yo'           # Haitian Creole
    'hu' = 'Vesetörténetek'          # Hungarian
    'hy' = 'Երիկամների Պատմություններ'  # Armenian
    'id' = 'Kisah Ginjal'            # Indonesian
    'ig' = 'Akụkọ Akụrụ'            # Igbo
    'ilo' = 'Dagiti Estoria ti Bekkel' # Ilocano
    'is' = 'Nýrnasögur'             # Icelandic
    'it' = 'Racconti Renali'        # Italian
    'ja' = '腎臓物語'                   # Japanese
    'jv' = 'Crita Ginjel'           # Javanese
    'ka' = 'თირკმლის ამბები'          # Georgian
    'kg' = 'Makanda ma Mpiku'        # Kongo
    'kk' = 'Бүйрек Ертегілері'       # Kazakh
    'kl' = 'Tarnip Oqaluttuaatit'    # Greenlandic
    'km' = 'រឿងរ៉ាវតម្រងឹង'            # Khmer
    'kn' = 'ಮೂತ್ರಪಿಂಡದ ಕಥೆಗಳು'        # Kannada
    'ko' = '신장 이야기'                 # Korean
    'ky' = 'Бөйрөк Окуялары'         # Kyrgyz
    'lb' = 'Nierengeschichten'       # Luxembourgish
    'lg' = 'Emboozi z''Ensigo'        # Luganda
    'ln' = 'Lisolo ya Mpiku'         # Lingala
    'lo' = 'ນິທານໄຕ'                   # Lao
    'lt' = 'Inkstų Pasakos'          # Lithuanian
    'lua' = 'Maluangu a Mpiku'       # Luba-Lulua
    'lv' = 'Nieru Stāsti'           # Latvian
    'mai' = 'गुर्दाक कहानी'           # Maithili
    'mg' = 'Tantara Voa'            # Malagasy
    'mk' = 'Бубрежни Приказни'       # Macedonian
    'ml' = 'വൃക്ക കഥകൾ'              # Malayalam
    'mn' = 'Бөөрний Түүхүүд'         # Mongolian
    'mr' = 'मूत्रपिंडाच्या कथा'        # Marathi
    'ms' = 'Kisah Buah Pinggang'     # Malay
    'mt' = 'Stejjer tar-Rieni'       # Maltese
    'my' = 'ကျောက်ကပ်ပုံပြင်များ'        # Burmese
    'nd' = 'Izindaba Zezinso'        # North Ndebele
    'ne' = 'मिर्गौलाका कथाहरू'        # Nepali
    'nl' = 'Nierverhalen'           # Dutch
    'no' = 'Nyrefortellinger'       # Norwegian
    'nr' = 'Izindaba Zezinso'        # South Ndebele
    'nso' = 'Dipale tša Dikoloto'    # Northern Sotho
    'ny' = 'Nkhani za Impsyo'        # Chichewa
    'om' = 'Seenaa Kalee'           # Oromo
    'or' = 'ବୃକ୍କର କାହାଣୀ'            # Odia
    'pa' = 'ਗੁਰਦੇ ਦੀਆਂ ਕਹਾਣੀਆਂ'      # Punjabi
    'pam' = 'Kwentung Batu'          # Pampanga
    'pl' = 'Opowieści Nerkowe'       # Polish
    'ps' = 'د پښتورګو کیسې'           # Pashto
    'pt' = 'Contos Renais'          # Portuguese
    'qu' = 'Riñon Willakuykuna'      # Quechua
    'rm' = 'Narrativas Renals'       # Romansh
    'rn' = 'Inkuru z''Impyiko'       # Kirundi
    'ro' = 'Poveștile Renale'       # Romanian
    'ru' = 'Почечные Сказки'        # Russian
    'rw' = 'Inkuru z''Impyiko'       # Kinyarwanda
    'sa' = 'वृक्ककथाः'               # Sanskrit
    'sd' = 'گردي جون ڪهاڻيون'         # Sindhi
    'se' = 'Báhppat'                # Northern Sami
    'sg' = 'Tî Mbëtï'               # Sango
    'si' = 'වකුගඩු කථා'               # Sinhala
    'sk' = 'Obličkové Príbehy'       # Slovak
    'sl' = 'Ledvične Zgodbe'         # Slovenian
    'sn' = 'Ngano dzeItsvo'          # Shona
    'so' = 'Sheekooyin Kelli'       # Somali
    'sq' = 'Përrallat e Veshkave'    # Albanian
    'sr' = 'Бубрежне Приче'         # Serbian
    'ss' = 'Tindzaba Tetikoloto'     # Swati
    'st' = 'Dipale tsa Dilakelo'     # Southern Sotho
    'su' = 'Dongéng Ginjal'         # Sundanese
    'sv' = 'Njurberättelser'        # Swedish
    'sw' = 'Hadithi za Figo'        # Swahili
    'ta' = 'சிறுநீரக கதைகள்'          # Tamil
    'te' = 'మూత్రపిండ కథలు'           # Telugu
    'tg' = 'Ҳикояҳои Гурда'          # Tajik
    'th' = 'เรื่องเล่าไต'              # Thai
    'ti' = 'ታሪኻት ኩላሊት'              # Tigrinya
    'tk' = 'Böwrek Ertekleri'        # Turkmen
    'tl' = 'Mga Kwentong Bato'       # Tagalog
    'tn' = 'Dikgang tsa Dikoloto'    # Tswana
    'tr' = 'Böbrek Hikayeleri'       # Turkish
    'ts' = 'Mitsheketo ya Swikoloto' # Tsonga
    'ug' = 'بۆرەك ھېكايىلىرى'          # Uyghur
    'uk' = 'Ниркові Казки'          # Ukrainian
    'ur' = 'گردے کی کہانیاں'          # Urdu
    'uz' = 'Buyrak Hikoyalari'       # Uzbek
    've' = 'Dzingano dza Tshikoloto' # Venda
    'vi' = 'Truyện Thận'            # Vietnamese
    'war' = 'Mga Istorya han Bato'   # Waray
    'wo' = 'Leeb yu Reer'           # Wolof
    'wuu' = '肾脏故事'                  # Wu Chinese
    'xh' = 'Iingxelo Zezintso'       # Xhosa
    'yo' = 'Àwọn Ìtàn Kíndìnrín'    # Yoruba
    'yue' = '腎臟故事'                 # Cantonese
    'zh' = '肾脏故事'                  # Chinese
    'zu' = 'Izindaba Zezinso'        # Zulu
}

# Get all PHP files in the languages directory
$languageFiles = Get-ChildItem -Path "G:\Môj disk\www\renaltales\languages" -Filter "*.php"

$updatedFiles = @()
$alreadyTranslated = @()
$notFound = @()

foreach ($file in $languageFiles) {
    $languageCode = [System.IO.Path]::GetFileNameWithoutExtension($file.Name)
    
    # Read the file content
    $content = Get-Content $file.FullName -Raw
    
    # Check if it currently has 'Renal Tales' in app_title
    if ($content -match "'app_title'\s*=>\s*'Renal Tales'") {
        # Check if we have a translation for this language
        if ($translations.ContainsKey($languageCode)) {
            $translation = $translations[$languageCode]
            
            # Replace 'Renal Tales' with the translation
            $newContent = $content -replace "'app_title'\s*=>\s*'Renal Tales'", "'app_title' => '$translation'"
            
            # Write back to file
            Set-Content -Path $file.FullName -Value $newContent -NoNewline
            
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
