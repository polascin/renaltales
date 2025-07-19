#!/usr/bin/env pwsh
<#
.SYNOPSIS
    Migration Script for RenalTales - Step 9: Testing and Migration Strategy
    
.DESCRIPTION
    This script migrates existing code references to the new simplified CSS architecture
    following BEM methodology and consolidated component structure.
    
.AUTHOR
    Generated for RenalTales Project Migration
    
.VERSION
    1.0.0
#>

param(
    [switch]$DryRun = $false,
    [switch]$Verbose = $false,
    [string]$BackupSuffix = "_pre_migration_backup"
)

# Color output functions
function Write-Success { param($Message) Write-Host "✅ $Message" -ForegroundColor Green }
function Write-Info { param($Message) Write-Host "ℹ️  $Message" -ForegroundColor Cyan }
function Write-Warning { param($Message) Write-Host "⚠️  $Message" -ForegroundColor Yellow }
function Write-Error { param($Message) Write-Host "❌ $Message" -ForegroundColor Red }

# Configuration
$ProjectRoot = $PWD.Path
$BackupDir = Join-Path $ProjectRoot "migration-backups-$(Get-Date -Format 'yyyyMMdd-HHmmss')"

Write-Info "Starting RenalTales Migration Script"
Write-Info "Project Root: $ProjectRoot"

if ($DryRun) {
    Write-Warning "DRY RUN MODE - No files will be modified"
}

# Create backup directory
if (-not $DryRun) {
    New-Item -ItemType Directory -Path $BackupDir -Force | Out-Null
    Write-Success "Created backup directory: $BackupDir"
}

# Define CSS file mappings (old → new)
$CssMappings = @{
    # Old navigation files
    'css/navigation.css' = 'css/components/navigation.css'
    'assets/css/navigation.css' = 'assets/css/components/navigation.css'
    'public/assets/css/navigation.css' = 'public/assets/css/components/navigation.css'
    
    # Component consolidations
    'css/buttons.css' = 'css/components/buttons.css'
    'css/forms.css' = 'css/components/forms.css'
    'css/cards.css' = 'css/components/cards.css'
    
    # Legacy files
    'css/responsive.css' = 'css/base/responsive.css'
    'css/error.css' = 'css/pages/error.css'
}

# Define BEM class mappings (old class → new BEM class)
$BemMappings = @{
    # Navigation classes
    'navbar' = 'nav'
    'navbar-nav' = 'nav__list'
    'nav-link' = 'nav__link'
    'nav-item' = 'nav__item'
    'navbar-brand' = 'nav__brand'
    'navbar-toggler' = 'nav__toggle'
    'navbar-collapse' = 'nav__collapse'
    
    # Breadcrumb classes
    'breadcrumb' = 'breadcrumbs'
    'breadcrumb-item' = 'breadcrumbs__item'
    'breadcrumb a' = 'breadcrumbs__link'
    'breadcrumb .active' = 'breadcrumbs__current'
    
    # Header classes
    'main-header' = 'header'
    'header-left' = 'header__left'
    'header-center' = 'header__center'
    'header-right' = 'header__right'
    'main-header-container' = 'header__container'
    
    # Language switcher
    'language-switcher' = 'lang-switcher'
    'language-selector' = 'lang-switcher__selector'
    'language-flag' = 'lang-switcher__flag'
    
    # Button classes
    'btn' = 'button'
    'btn-primary' = 'button--primary'
    'btn-secondary' = 'button--secondary'
    'btn-success' = 'button--success'
    'btn-danger' = 'button--danger'
    'btn-warning' = 'button--warning'
    'btn-info' = 'button--info'
    'btn-light' = 'button--light'
    'btn-dark' = 'button--dark'
    'btn-outline-primary' = 'button--outline-primary'
    'btn-lg' = 'button--large'
    'btn-sm' = 'button--small'
    
    # Form classes
    'form-control' = 'form__input'
    'form-group' = 'form__group'
    'form-label' = 'form__label'
    'form-text' = 'form__help-text'
    'is-valid' = 'form__input--valid'
    'is-invalid' = 'form__input--error'
    
    # Card classes
    'card' = 'card'
    'card-header' = 'card__header'
    'card-body' = 'card__body'
    'card-footer' = 'card__footer'
    'card-title' = 'card__title'
    'card-text' = 'card__text'
}

# Function to backup a file
function Backup-File {
    param([string]$FilePath)
    
    if (-not $DryRun) {
        $RelativePath = [System.IO.Path]::GetRelativePath($ProjectRoot, $FilePath)
        $BackupPath = Join-Path $BackupDir $RelativePath
        $BackupParentDir = Split-Path $BackupPath -Parent
        
        if (-not (Test-Path $BackupParentDir)) {
            New-Item -ItemType Directory -Path $BackupParentDir -Force | Out-Null
        }
        
        Copy-Item $FilePath $BackupPath -Force
        if ($Verbose) { Write-Info "Backed up: $RelativePath" }
    }
}

# Function to update CSS references in files
function Update-CssReferences {
    param([array]$Files)
    
    Write-Info "Updating CSS references in $(($Files | Measure-Object).Count) files..."
    
    foreach ($file in $Files) {
        $content = Get-Content $file.FullName -Raw
        $originalContent = $content
        $modified = $false
        
        foreach ($oldPath in $CssMappings.Keys) {
            $newPath = $CssMappings[$oldPath]
            if ($content -match [regex]::Escape($oldPath)) {
                $content = $content -replace [regex]::Escape($oldPath), $newPath
                $modified = $true
                if ($Verbose) { Write-Info "  Updated: $oldPath → $newPath in $($file.Name)" }
            }
        }
        
        if ($modified) {
            Backup-File $file.FullName
            if (-not $DryRun) {
                Set-Content -Path $file.FullName -Value $content
            }
            Write-Success "Updated CSS references in: $($file.Name)"
        }
    }
}

# Function to update BEM class names in files
function Update-BemClasses {
    param([array]$Files)
    
    Write-Info "Updating BEM class names in $(($Files | Measure-Object).Count) files..."
    
    foreach ($file in $Files) {
        $content = Get-Content $file.FullName -Raw
        $originalContent = $content
        $modified = $false
        
        foreach ($oldClass in $BemMappings.Keys) {
            $newClass = $BemMappings[$oldClass]
            
            # Handle different class attribute patterns
            $patterns = @(
                "class=`"([^`"]*\b)$([regex]::Escape($oldClass))(\b[^`"]*)`"",
                "class='([^']*\b)$([regex]::Escape($oldClass))(\b[^']*)'",
                "classList\.add\('$([regex]::Escape($oldClass))'\)",
                "classList\.remove\('$([regex]::Escape($oldClass))'\)",
                "\.$([regex]::Escape($oldClass))\b"
            )
            
            foreach ($pattern in $patterns) {
                if ($content -match $pattern) {
                    $content = $content -replace $pattern, { 
                        param($match)
                        $match.Value -replace [regex]::Escape($oldClass), $newClass
                    }
                    $modified = $true
                    if ($Verbose) { Write-Info "  Updated: $oldClass → $newClass in $($file.Name)" }
                }
            }
        }
        
        if ($modified) {
            Backup-File $file.FullName
            if (-not $DryRun) {
                Set-Content -Path $file.FullName -Value $content
            }
            Write-Success "Updated BEM classes in: $($file.Name)"
        }
    }
}

# Function to update import statements
function Update-ImportStatements {
    param([array]$Files)
    
    Write-Info "Updating import statements..."
    
    $importMappings = @{
        # CSS imports
        "@import 'navigation.css'" = "@import 'components/navigation.css'"
        "@import './navigation.css'" = "@import './components/navigation.css'"
        "@import url('navigation.css')" = "@import url('components/navigation.css')"
        
        # JavaScript imports
        "import './css/navigation.css'" = "import './css/components/navigation.css'"
        "require('./css/navigation.css')" = "require('./css/components/navigation.css')"
    }
    
    foreach ($file in $Files) {
        $content = Get-Content $file.FullName -Raw
        $modified = $false
        
        foreach ($oldImport in $importMappings.Keys) {
            $newImport = $importMappings[$oldImport]
            if ($content -match [regex]::Escape($oldImport)) {
                $content = $content -replace [regex]::Escape($oldImport), $newImport
                $modified = $true
                if ($Verbose) { Write-Info "  Updated import: $oldImport → $newImport in $($file.Name)" }
            }
        }
        
        if ($modified) {
            Backup-File $file.FullName
            if (-not $DryRun) {
                Set-Content -Path $file.FullName -Value $content
            }
            Write-Success "Updated imports in: $($file.Name)"
        }
    }
}

# Main migration process
try {
    Write-Info "=== Starting Migration Process ==="
    
    # Find all relevant files
    $phpFiles = Get-ChildItem -Path $ProjectRoot -Filter "*.php" -Recurse | Where-Object { 
        $_.FullName -notlike "*vendor*" -and 
        $_.FullName -notlike "*node_modules*" -and
        $_.FullName -notlike "*backup*"
    }
    
    $cssFiles = Get-ChildItem -Path $ProjectRoot -Filter "*.css" -Recurse | Where-Object { 
        $_.FullName -notlike "*vendor*" -and 
        $_.FullName -notlike "*node_modules*" -and
        $_.FullName -notlike "*backup*"
    }
    
    $jsFiles = Get-ChildItem -Path $ProjectRoot -Filter "*.js" -Recurse | Where-Object { 
        $_.FullName -notlike "*vendor*" -and 
        $_.FullName -notlike "*node_modules*" -and
        $_.FullName -notlike "*backup*"
    }
    
    $htmlFiles = Get-ChildItem -Path $ProjectRoot -Filter "*.html" -Recurse | Where-Object { 
        $_.FullName -notlike "*vendor*" -and 
        $_.FullName -notlike "*node_modules*" -and
        $_.FullName -notlike "*backup*"
    }
    
    $allFiles = @($phpFiles) + @($cssFiles) + @($jsFiles) + @($htmlFiles)
    
    Write-Info "Found $(($phpFiles | Measure-Object).Count) PHP files"
    Write-Info "Found $(($cssFiles | Measure-Object).Count) CSS files"
    Write-Info "Found $(($jsFiles | Measure-Object).Count) JS files"
    Write-Info "Found $(($htmlFiles | Measure-Object).Count) HTML files"
    
    # Step 1: Update CSS file references
    Update-CssReferences -Files $allFiles
    
    # Step 2: Update BEM class names in PHP/HTML files
    $templateFiles = @($phpFiles) + @($htmlFiles)
    Update-BemClasses -Files $templateFiles
    
    # Step 3: Update import statements in CSS/JS files
    $importFiles = @($cssFiles) + @($jsFiles)
    Update-ImportStatements -Files $importFiles
    
    Write-Success "=== Migration Process Completed Successfully ==="
    
    if (-not $DryRun) {
        Write-Info "Backup created at: $BackupDir"
        Write-Info "To rollback changes, restore files from backup directory"
    }
    
    # Generate migration report
    $reportPath = Join-Path $ProjectRoot "migration-report-$(Get-Date -Format 'yyyyMMdd-HHmmss').txt"
    $report = @"
RenalTales Migration Report
==========================
Generated: $(Get-Date)
Dry Run: $DryRun

Files Processed:
- PHP Files: $(($phpFiles | Measure-Object).Count)
- CSS Files: $(($cssFiles | Measure-Object).Count) 
- JS Files: $(($jsFiles | Measure-Object).Count)
- HTML Files: $(($htmlFiles | Measure-Object).Count)

CSS Mappings Applied:
$($CssMappings.GetEnumerator() | ForEach-Object { "- $($_.Key) → $($_.Value)" } | Out-String)

BEM Class Mappings Applied:
$($BemMappings.GetEnumerator() | ForEach-Object { "- $($_.Key) → $($_.Value)" } | Out-String)

Backup Location: $BackupDir

Next Steps:
1. Test the application thoroughly
2. Run CSS validation
3. Check responsive design
4. Verify all language variations
5. Run performance tests
"@
    
    if (-not $DryRun) {
        Set-Content -Path $reportPath -Value $report
        Write-Success "Migration report saved to: $reportPath"
    } else {
        Write-Info "Migration report (DRY RUN):"
        Write-Host $report -ForegroundColor Gray
    }
    
} catch {
    Write-Error "Migration failed: $($_.Exception.Message)"
    Write-Error "Stack trace: $($_.ScriptStackTrace)"
    exit 1
}

Write-Success "Migration script completed successfully!"
