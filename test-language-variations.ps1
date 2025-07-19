#!/usr/bin/env pwsh
<#
.SYNOPSIS
    Language Testing Script for RenalTales - Step 9: Testing and Migration Strategy
    
.DESCRIPTION
    This script tests all language variations to ensure translations are properly applied
    and displayed correctly across different components and pages.
    
.AUTHOR
    Generated for RenalTales Project Migration
    
.VERSION
    1.0.0
#>

param(
    [string]$BaseUrl = "http://localhost",
    [int]$Port = 8000,
    [switch]$Verbose = $false,
    [string]$OutputDir = "testing\language-test-results"
)

# Color output functions
function Write-Success { param($Message) Write-Host "✅ $Message" -ForegroundColor Green }
function Write-Info { param($Message) Write-Host "ℹ️  $Message" -ForegroundColor Cyan }
function Write-Warning { param($Message) Write-Host "⚠️  $Message" -ForegroundColor Yellow }
function Write-Error { param($Message) Write-Host "❌ $Message" -ForegroundColor Red }

# Configuration
$ProjectRoot = $PWD.Path
$FullOutputDir = Join-Path $ProjectRoot $OutputDir
$TestUrl = "${BaseUrl}:${Port}"

Write-Info "Starting Language Variation Testing"
Write-Info "Project Root: $ProjectRoot"
Write-Info "Test URL: $TestUrl"
Write-Info "Output Directory: $FullOutputDir"

# Create output directory
if (-not (Test-Path $FullOutputDir)) {
    New-Item -ItemType Directory -Path $FullOutputDir -Force | Out-Null
    Write-Success "Created output directory: $FullOutputDir"
}

# Define supported languages with their details
$SupportedLanguages = @{
    'en' = @{ Name = 'English'; Direction = 'ltr'; Script = 'Latin' }
    'sk' = @{ Name = 'Slovak'; Direction = 'ltr'; Script = 'Latin' }
    'de' = @{ Name = 'German'; Direction = 'ltr'; Script = 'Latin' }
    'fr' = @{ Name = 'French'; Direction = 'ltr'; Script = 'Latin' }
    'es' = @{ Name = 'Spanish'; Direction = 'ltr'; Script = 'Latin' }
    'it' = @{ Name = 'Italian'; Direction = 'ltr'; Script = 'Latin' }
    'pt' = @{ Name = 'Portuguese'; Direction = 'ltr'; Script = 'Latin' }
    'ru' = @{ Name = 'Russian'; Direction = 'ltr'; Script = 'Cyrillic' }
    'uk' = @{ Name = 'Ukrainian'; Direction = 'ltr'; Script = 'Cyrillic' }
    'pl' = @{ Name = 'Polish'; Direction = 'ltr'; Script = 'Latin' }
    'cs' = @{ Name = 'Czech'; Direction = 'ltr'; Script = 'Latin' }
    'hu' = @{ Name = 'Hungarian'; Direction = 'ltr'; Script = 'Latin' }
    'ro' = @{ Name = 'Romanian'; Direction = 'ltr'; Script = 'Latin' }
    'nl' = @{ Name = 'Dutch'; Direction = 'ltr'; Script = 'Latin' }
    'sv' = @{ Name = 'Swedish'; Direction = 'ltr'; Script = 'Latin' }
    'no' = @{ Name = 'Norwegian'; Direction = 'ltr'; Script = 'Latin' }
    'da' = @{ Name = 'Danish'; Direction = 'ltr'; Script = 'Latin' }
    'fi' = @{ Name = 'Finnish'; Direction = 'ltr'; Script = 'Latin' }
    'zh' = @{ Name = 'Chinese'; Direction = 'ltr'; Script = 'CJK' }
    'ja' = @{ Name = 'Japanese'; Direction = 'ltr'; Script = 'CJK' }
    'ko' = @{ Name = 'Korean'; Direction = 'ltr'; Script = 'CJK' }
    'ar' = @{ Name = 'Arabic'; Direction = 'rtl'; Script = 'Arabic' }
    'fa' = @{ Name = 'Persian'; Direction = 'rtl'; Script = 'Arabic' }
    'ur' = @{ Name = 'Urdu'; Direction = 'rtl'; Script = 'Arabic' }
    'he' = @{ Name = 'Hebrew'; Direction = 'rtl'; Script = 'Hebrew' }
    'hi' = @{ Name = 'Hindi'; Direction = 'ltr'; Script = 'Devanagari' }
    'bn' = @{ Name = 'Bengali'; Direction = 'ltr'; Script = 'Bengali' }
    'ta' = @{ Name = 'Tamil'; Direction = 'ltr'; Script = 'Tamil' }
    'te' = @{ Name = 'Telugu'; Direction = 'ltr'; Script = 'Telugu' }
    'gu' = @{ Name = 'Gujarati'; Direction = 'ltr'; Script = 'Gujarati' }
    'kn' = @{ Name = 'Kannada'; Direction = 'ltr'; Script = 'Kannada' }
    'ml' = @{ Name = 'Malayalam'; Direction = 'ltr'; Script = 'Malayalam' }
    'th' = @{ Name = 'Thai'; Direction = 'ltr'; Script = 'Thai' }
    'vi' = @{ Name = 'Vietnamese'; Direction = 'ltr'; Script = 'Latin' }
    'id' = @{ Name = 'Indonesian'; Direction = 'ltr'; Script = 'Latin' }
    'ms' = @{ Name = 'Malay'; Direction = 'ltr'; Script = 'Latin' }
    'tl' = @{ Name = 'Tagalog'; Direction = 'ltr'; Script = 'Latin' }
    'sw' = @{ Name = 'Swahili'; Direction = 'ltr'; Script = 'Latin' }
    'am' = @{ Name = 'Amharic'; Direction = 'ltr'; Script = 'Ethiopic' }
    'zu' = @{ Name = 'Zulu'; Direction = 'ltr'; Script = 'Latin' }
    'xh' = @{ Name = 'Xhosa'; Direction = 'ltr'; Script = 'Latin' }
    'af' = @{ Name = 'Afrikaans'; Direction = 'ltr'; Script = 'Latin' }
}

# Test pages
$TestPages = @(
    @{ Path = '/'; Name = 'Home Page' }
    @{ Path = '/about'; Name = 'About Page' }
    @{ Path = '/contact'; Name = 'Contact Page' }
    @{ Path = '/error'; Name = 'Error Page' }
)

# Key translation elements to check
$TranslationElements = @(
    'title'
    'nav'
    'breadcrumb'
    'button'
    'form'
    'footer'
    'language-switcher'
)

# Function to test language file existence
function Test-LanguageFiles {
    param([array]$Languages)
    
    Write-Info "Testing language file existence..."
    $results = @{}
    
    foreach ($lang in $Languages) {
        $langFile = Join-Path $ProjectRoot "resources\lang\$lang.php"
        $exists = Test-Path $langFile
        
        $results[$lang] = @{
            FileExists = $exists
            FilePath = $langFile
            Error = if (-not $exists) { "Language file not found" } else { $null }
        }
        
        if ($exists) {
            Write-Success "✓ $lang.php exists"
            
            # Check file content
            try {
                $content = Get-Content $langFile -Raw
                $hasTranslations = $content -match "return\s*\["
                
                if (-not $hasTranslations) {
                    $results[$lang].Error = "Language file appears to be empty or malformed"
                    Write-Warning "⚠️  $lang.php appears to be empty or malformed"
                }
            } catch {
                $results[$lang].Error = "Could not read language file: $($_.Exception.Message)"
                Write-Error "❌ Could not read $lang.php: $($_.Exception.Message)"
            }
        } else {
            Write-Warning "⚠️  $lang.php missing"
        }
    }
    
    return $results
}

# Function to test HTTP endpoints
function Test-LanguageEndpoints {
    param([array]$Languages, [array]$Pages)
    
    Write-Info "Testing language endpoints..."
    $results = @{}
    
    foreach ($lang in $Languages) {
        $results[$lang] = @{}
        
        foreach ($page in $Pages) {
            $url = "${TestUrl}$($page.Path)?lang=$lang"
            
            try {
                $response = Invoke-WebRequest -Uri $url -Method GET -TimeoutSec 10 -UseBasicParsing
                
                $results[$lang][$page.Name] = @{
                    StatusCode = $response.StatusCode
                    ContentLength = $response.Content.Length
                    Success = $response.StatusCode -eq 200
                    Error = $null
                    HasTranslations = $false
                }
                
                # Basic check for language-specific content
                if ($response.StatusCode -eq 200) {
                    $hasLangAttr = $response.Content -match "lang=`"$lang`""
                    $hasContent = $response.Content.Length -gt 100
                    
                    $results[$lang][$page.Name].HasTranslations = $hasLangAttr -and $hasContent
                    
                    if ($Verbose) {
                        Write-Info "  $lang - $($page.Name): $($response.StatusCode) ($($response.Content.Length) bytes)"
                    }
                } else {
                    Write-Warning "  $lang - $($page.Name): HTTP $($response.StatusCode)"
                }
                
            } catch {
                $results[$lang][$page.Name] = @{
                    StatusCode = 0
                    ContentLength = 0
                    Success = $false
                    Error = $_.Exception.Message
                    HasTranslations = $false
                }
                
                Write-Error "  $lang - $($page.Name): $($_.Exception.Message)"
            }
        }
    }
    
    return $results
}

# Function to test breadcrumb translations
function Test-BreadcrumbTranslations {
    param([array]$Languages)
    
    Write-Info "Testing breadcrumb translations..."
    $results = @{}
    
    foreach ($lang in $Languages) {
        $langFile = Join-Path $ProjectRoot "resources\lang\$lang.php"
        
        if (Test-Path $langFile) {
            try {
                $content = Get-Content $langFile -Raw
                
                # Check for breadcrumb-related translations
                $hasBreadcrumbHome = $content -match "'breadcrumb\.home'"
                $hasBreadcrumbSeparator = $content -match "'breadcrumb\.separator'"
                
                $results[$lang] = @{
                    HasBreadcrumbHome = $hasBreadcrumbHome
                    HasBreadcrumbSeparator = $hasBreadcrumbSeparator
                    Score = ([int]$hasBreadcrumbHome + [int]$hasBreadcrumbSeparator)
                }
                
                if ($Verbose) {
                    Write-Info "  ${lang}: Breadcrumb translations score $($results[$lang].Score)/2"
                }
            } catch {
                $results[$lang] = @{
                    HasBreadcrumbHome = $false
                    HasBreadcrumbSeparator = $false
                    Score = 0
                    Error = $_.Exception.Message
                }
            }
        } else {
            $results[$lang] = @{
                HasBreadcrumbHome = $false
                HasBreadcrumbSeparator = $false
                Score = 0
                Error = "Language file not found"
            }
        }
    }
    
    return $results
}

# Function to test RTL language support
function Test-RtlSupport {
    param([array]$RtlLanguages)
    
    Write-Info "Testing RTL language support..."
    $results = @{}
    
    # Check CSS files for RTL support
    $cssFiles = Get-ChildItem -Path $ProjectRoot -Filter "*.css" -Recurse | Where-Object { 
        $_.FullName -notlike "*vendor*" -and 
        $_.FullName -notlike "*node_modules*" -and
        $_.FullName -notlike "*backup*"
    }
    
    $rtlCssPatterns = @(
        'direction:\s*rtl',
        'text-align:\s*right',
        '\[dir="rtl"\]',
        '\.rtl\b',
        'margin-right',
        'padding-right'
    )
    
    $hasRtlSupport = $false
    
    foreach ($cssFile in $cssFiles) {
        $content = Get-Content $cssFile.FullName -Raw
        
        foreach ($pattern in $rtlCssPatterns) {
            if ($content -match $pattern) {
                $hasRtlSupport = $true
                break
            }
        }
        
        if ($hasRtlSupport) { break }
    }
    
    foreach ($lang in $RtlLanguages) {
        $results[$lang] = @{
            HasCssSupport = $hasRtlSupport
            Direction = 'rtl'
            IsSupported = $hasRtlSupport
        }
        
        if ($Verbose) {
            Write-Info "  ${lang}: RTL CSS support " + $(if ($hasRtlSupport) { "✓" } else { "✗" })
        }
    }
    
    return $results
}

# Function to generate comprehensive report
function Generate-LanguageReport {
    param(
        [hashtable]$FileResults,
        [hashtable]$EndpointResults,
        [hashtable]$BreadcrumbResults,
        [hashtable]$RtlResults,
        [array]$Languages
    )
    
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $reportPath = Join-Path $FullOutputDir "language-test-report.html"
    
    $html = @"
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RenalTales Language Testing Report</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            background-color: #f8fafc;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            text-align: center;
        }
        .summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .summary-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        .summary-number {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
            display: block;
        }
        .section {
            background: white;
            margin-bottom: 2rem;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .section-header {
            background: #f1f5f9;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 600;
            color: #334155;
        }
        .section-content {
            padding: 1.5rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }
        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        th {
            background-color: #f8fafc;
            font-weight: 600;
            color: #475569;
        }
        .status-success { color: #059669; font-weight: 600; }
        .status-warning { color: #d97706; font-weight: 600; }
        .status-error { color: #dc2626; font-weight: 600; }
        .language-name { font-weight: 600; }
        .rtl { direction: rtl; }
        .script-tag {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            background: #e0e7ff;
            color: #3730a3;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981, #059669);
            transition: width 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🌐 RenalTales Language Testing Report</h1>
        <p>Generated on $timestamp</p>
        <p>Testing $($Languages.Count) languages across multiple components</p>
    </div>
    
    <div class="summary">
        <div class="summary-card">
            <span class="summary-number">$($Languages.Count)</span>
            <div>Total Languages</div>
        </div>
        <div class="summary-card">
            <span class="summary-number">$(($FileResults.Values | Where-Object { $_.FileExists }).Count)</span>
            <div>Language Files Found</div>
        </div>
        <div class="summary-card">
            <span class="summary-number">$(($RtlResults.Keys | Measure-Object).Count)</span>
            <div>RTL Languages</div>
        </div>
        <div class="summary-card">
            <span class="summary-number">$($TestPages.Count)</span>
            <div>Pages Tested</div>
        </div>
    </div>
"@

    # Language Files Section
    $html += @"
    <div class="section">
        <div class="section-header">📁 Language File Status</div>
        <div class="section-content">
            <table>
                <thead>
                    <tr>
                        <th>Language</th>
                        <th>Code</th>
                        <th>Script</th>
                        <th>Direction</th>
                        <th>File Status</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
"@

    foreach ($lang in $Languages) {
        $langInfo = $SupportedLanguages[$lang]
        $fileInfo = $FileResults[$lang]
        $status = if ($fileInfo.FileExists -and -not $fileInfo.Error) { "✅ Found" } 
                 elseif ($fileInfo.FileExists -and $fileInfo.Error) { "⚠️ Issues" }
                 else { "❌ Missing" }
        
        $statusClass = if ($fileInfo.FileExists -and -not $fileInfo.Error) { "status-success" } 
                      elseif ($fileInfo.FileExists -and $fileInfo.Error) { "status-warning" }
                      else { "status-error" }
        
        $direction = if ($langInfo.Direction -eq 'rtl') { "rtl" } else { "" }
        
        $html += @"
                    <tr>
                        <td class="language-name $direction">$($langInfo.Name)</td>
                        <td>$lang</td>
                        <td><span class="script-tag">$($langInfo.Script)</span></td>
                        <td>$($langInfo.Direction.ToUpper())</td>
                        <td class="$statusClass">$status</td>
                        <td>$($fileInfo.Error -replace '<', '&lt;' -replace '>', '&gt;')</td>
                    </tr>
"@
    }

    $html += @"
                </tbody>
            </table>
        </div>
    </div>
"@

    # Breadcrumb Support Section
    $html += @"
    <div class="section">
        <div class="section-header">🧭 Breadcrumb Translation Support</div>
        <div class="section-content">
            <table>
                <thead>
                    <tr>
                        <th>Language</th>
                        <th>Home Translation</th>
                        <th>Separator Translation</th>
                        <th>Completion</th>
                        <th>Progress</th>
                    </tr>
                </thead>
                <tbody>
"@

    foreach ($lang in $Languages) {
        $breadcrumbInfo = $BreadcrumbResults[$lang]
        if ($breadcrumbInfo) {
            $homeStatus = if ($breadcrumbInfo.HasBreadcrumbHome) { "✅" } else { "❌" }
            $separatorStatus = if ($breadcrumbInfo.HasBreadcrumbSeparator) { "✅" } else { "❌" }
            $completion = [math]::Round(($breadcrumbInfo.Score / 2) * 100, 0)
            
            $html += @"
                    <tr>
                        <td class="language-name">$($SupportedLanguages[$lang].Name)</td>
                        <td>$homeStatus</td>
                        <td>$separatorStatus</td>
                        <td>$completion%</td>
                        <td>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: $completion%"></div>
                            </div>
                        </td>
                    </tr>
"@
        }
    }

    $html += @"
                </tbody>
            </table>
        </div>
    </div>
"@

    # RTL Support Section if RTL languages exist
    if ($RtlResults.Keys.Count -gt 0) {
        $html += @"
    <div class="section">
        <div class="section-header">🔄 RTL Language Support</div>
        <div class="section-content">
            <table>
                <thead>
                    <tr>
                        <th>Language</th>
                        <th>CSS Support</th>
                        <th>Direction</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
"@
        
        foreach ($lang in $RtlResults.Keys) {
            $rtlInfo = $RtlResults[$lang]
            $cssSupport = if ($rtlInfo.HasCssSupport) { "✅ Supported" } else { "❌ Not Found" }
            $statusClass = if ($rtlInfo.IsSupported) { "status-success" } else { "status-error" }
            
            $html += @"
                    <tr>
                        <td class="language-name rtl">$($SupportedLanguages[$lang].Name)</td>
                        <td class="$statusClass">$cssSupport</td>
                        <td>$($rtlInfo.Direction.ToUpper())</td>
                        <td class="$statusClass">$(if ($rtlInfo.IsSupported) { "✅ Ready" } else { "⚠️ Needs CSS" })</td>
                    </tr>
"@
        }
        
        $html += @"
                </tbody>
            </table>
        </div>
    </div>
"@
    }

    $html += @"
</body>
</html>
"@

    Set-Content -Path $reportPath -Value $html
    Write-Success "Language report saved to: $reportPath"
    
    return $reportPath
}

# Main testing process
try {
    Write-Info "=== Starting Language Testing Process ==="
    
    $languages = $SupportedLanguages.Keys | Sort-Object
    
    # Test 1: Language file existence and content
    Write-Info "Step 1: Testing language files..."
    $fileResults = Test-LanguageFiles -Languages $languages
    
    # Test 2: Breadcrumb translations
    Write-Info "Step 2: Testing breadcrumb translations..."
    $breadcrumbResults = Test-BreadcrumbTranslations -Languages $languages
    
    # Test 3: RTL support
    Write-Info "Step 3: Testing RTL language support..."
    $rtlLanguages = $SupportedLanguages.Keys | Where-Object { $SupportedLanguages[$_].Direction -eq 'rtl' }
    $rtlResults = Test-RtlSupport -RtlLanguages $rtlLanguages
    
    # Test 4: HTTP endpoints (if server is running)
    Write-Info "Step 4: Testing HTTP endpoints..."
    try {
        $endpointResults = Test-LanguageEndpoints -Languages @('en', 'sk', 'de', 'fr', 'ar') -Pages $TestPages
    } catch {
        Write-Warning "HTTP endpoint testing skipped - server may not be running"
        $endpointResults = @{}
    }
    
    # Generate comprehensive report
    Write-Info "Step 5: Generating comprehensive report..."
    $reportPath = Generate-LanguageReport -FileResults $fileResults -EndpointResults $endpointResults -BreadcrumbResults $breadcrumbResults -RtlResults $rtlResults -Languages $languages
    
    # Summary statistics
    $totalLanguages = $languages.Count
    $existingFiles = ($fileResults.Values | Where-Object { $_.FileExists }).Count
    $workingBreadcrumbs = ($breadcrumbResults.Values | Where-Object { $_.Score -eq 2 }).Count
    $rtlReady = ($rtlResults.Values | Where-Object { $_.IsSupported }).Count
    
    Write-Success "=== Language Testing Completed ==="
    Write-Info "📊 Summary:"
    Write-Info "   Total Languages: $totalLanguages"
    Write-Info "   Language Files Found: $existingFiles/$totalLanguages"
    Write-Info "   Complete Breadcrumb Translations: $workingBreadcrumbs/$totalLanguages"
    Write-Info "   RTL Languages Ready: $rtlReady/$($rtlLanguages.Count)"
    Write-Info "   Report saved to: $reportPath"
    
    # Generate action items
    $missingFiles = $fileResults.Keys | Where-Object { -not $fileResults[$_].FileExists }
    $incompleteBreadcrumbs = $breadcrumbResults.Keys | Where-Object { $breadcrumbResults[$_].Score -lt 2 }
    $rtlNotReady = $rtlResults.Keys | Where-Object { -not $rtlResults[$_].IsSupported }
    
    if ($missingFiles.Count -gt 0 -or $incompleteBreadcrumbs.Count -gt 0 -or $rtlNotReady.Count -gt 0) {
        Write-Info ""
        Write-Info "🔧 Action Items:"
        
        if ($missingFiles.Count -gt 0) {
            Write-Warning "   Create missing language files: $($missingFiles -join ', ')"
        }
        
        if ($incompleteBreadcrumbs.Count -gt 0) {
            Write-Warning "   Complete breadcrumb translations for: $($incompleteBreadcrumbs -join ', ')"
        }
        
        if ($rtlNotReady.Count -gt 0) {
            Write-Warning "   Add RTL CSS support for: $($rtlNotReady -join ', ')"
        }
    }
    
} catch {
    Write-Error "Language testing failed: $($_.Exception.Message)"
    Write-Error "Stack trace: $($_.ScriptStackTrace)"
    exit 1
}

Write-Success "Language testing completed successfully!"
