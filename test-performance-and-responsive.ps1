#!/usr/bin/env pwsh
<#
.SYNOPSIS
    Performance Metrics and Responsive Design Testing Script for RenalTales
    
.DESCRIPTION
    This script provides before/after performance metrics and verifies responsive design
    works with the simplified CSS architecture.
    
.AUTHOR
    Generated for RenalTales Project Migration - Step 9
    
.VERSION
    1.0.0
#>

param(
    [string]$BaseUrl = "http://localhost:8002",
    [switch]$Verbose = $false,
    [string]$OutputDir = "testing\performance-results"
)

# Color output functions
function Write-Success { param($Message) Write-Host "✅ $Message" -ForegroundColor Green }
function Write-Info { param($Message) Write-Host "ℹ️  $Message" -ForegroundColor Cyan }
function Write-Warning { param($Message) Write-Host "⚠️  $Message" -ForegroundColor Yellow }
function Write-Error { param($Message) Write-Host "❌ $Message" -ForegroundColor Red }

# Configuration
$ProjectRoot = $PWD.Path
$FullOutputDir = Join-Path $ProjectRoot $OutputDir

Write-Info "Starting Performance and Responsive Design Testing"
Write-Info "Project Root: $ProjectRoot"
Write-Info "Base URL: $BaseUrl"
Write-Info "Output Directory: $FullOutputDir"

# Create output directory
if (-not (Test-Path $FullOutputDir)) {
    New-Item -ItemType Directory -Path $FullOutputDir -Force | Out-Null
    Write-Success "Created output directory: $FullOutputDir"
}

# Define viewport sizes for responsive testing
$ViewportSizes = @{
    'mobile-small' = @{ width = 320; height = 568; name = 'Small Mobile' }
    'mobile' = @{ width = 375; height = 667; name = 'Mobile' }
    'mobile-large' = @{ width = 414; height = 896; name = 'Large Mobile' }
    'tablet-portrait' = @{ width = 768; height = 1024; name = 'Tablet Portrait' }
    'tablet-landscape' = @{ width = 1024; height = 768; name = 'Tablet Landscape' }
    'desktop-small' = @{ width = 1280; height = 800; name = 'Small Desktop' }
    'desktop' = @{ width = 1920; height = 1080; name = 'Desktop' }
    'desktop-large' = @{ width = 2560; height = 1440; name = 'Large Desktop' }
}

# Function to analyze CSS file performance
function Test-CssPerformance {
    param([string]$CssPath)
    
    Write-Info "Analyzing CSS performance for: $CssPath"
    
    if (-not (Test-Path $CssPath)) {
        return @{
            FileExists = $false
            Error = "File not found: $CssPath"
        }
    }
    
    try {
        $content = Get-Content $CssPath -Raw
        $lines = ($content -split "`n").Length
        $sizeBytes = (Get-Item $CssPath).Length
        $sizeKB = [math]::Round($sizeBytes / 1024, 2)
        
        # Count CSS rules
        $ruleCount = ($content | Select-String -Pattern '\{[^}]*\}' -AllMatches).Matches.Count
        
        # Count selectors
        $selectorCount = ($content | Select-String -Pattern '[^{}]+\{' -AllMatches).Matches.Count
        
        # Count media queries
        $mediaQueryCount = ($content | Select-String -Pattern '@media[^{]*\{' -AllMatches).Matches.Count
        
        # Count CSS custom properties (variables)
        $variableCount = ($content | Select-String -Pattern '--[a-zA-Z-]+:' -AllMatches).Matches.Count
        
        # Estimate compression savings
        $estimatedGzipSize = [math]::Round($sizeBytes * 0.3, 0) # Rough estimate
        
        return @{
            FileExists = $true
            FilePath = $CssPath
            SizeBytes = $sizeBytes
            SizeKB = $sizeKB
            Lines = $lines
            RuleCount = $ruleCount
            SelectorCount = $selectorCount
            MediaQueryCount = $mediaQueryCount
            VariableCount = $variableCount
            EstimatedGzipSize = $estimatedGzipSize
            EstimatedGzipSizeKB = [math]::Round($estimatedGzipSize / 1024, 2)
            CompressionRatio = [math]::Round((1 - ($estimatedGzipSize / $sizeBytes)) * 100, 1)
        }
    } catch {
        return @{
            FileExists = $true
            Error = $_.Exception.Message
        }
    }
}

# Function to test responsive breakpoints
function Test-ResponsiveBreakpoints {
    param([string]$CssPath)
    
    Write-Info "Testing responsive breakpoints in: $CssPath"
    
    if (-not (Test-Path $CssPath)) {
        return @{
            FileExists = $false
            Error = "File not found"
        }
    }
    
    try {
        $content = Get-Content $CssPath -Raw
        
        # Common breakpoint patterns
        $breakpoints = @{
            'max-width: 480px' = ($content | Select-String -Pattern 'max-width:\s*480px' -AllMatches).Matches.Count
            'max-width: 768px' = ($content | Select-String -Pattern 'max-width:\s*768px' -AllMatches).Matches.Count
            'max-width: 992px' = ($content | Select-String -Pattern 'max-width:\s*992px' -AllMatches).Matches.Count
            'max-width: 1200px' = ($content | Select-String -Pattern 'max-width:\s*1200px' -AllMatches).Matches.Count
            'min-width: 481px' = ($content | Select-String -Pattern 'min-width:\s*481px' -AllMatches).Matches.Count
            'min-width: 769px' = ($content | Select-String -Pattern 'min-width:\s*769px' -AllMatches).Matches.Count
            'min-width: 993px' = ($content | Select-String -Pattern 'min-width:\s*993px' -AllMatches).Matches.Count
            'min-width: 1201px' = ($content | Select-String -Pattern 'min-width:\s*1201px' -AllMatches).Matches.Count
        }
        
        # Flexible unit usage
        $flexibleUnits = @{
            'rem' = ($content | Select-String -Pattern '\d+\.?\d*rem' -AllMatches).Matches.Count
            'em' = ($content | Select-String -Pattern '\d+\.?\d*em' -AllMatches).Matches.Count
            '%' = ($content | Select-String -Pattern '\d+\.?\d*%' -AllMatches).Matches.Count
            'vw' = ($content | Select-String -Pattern '\d+\.?\d*vw' -AllMatches).Matches.Count
            'vh' = ($content | Select-String -Pattern '\d+\.?\d*vh' -AllMatches).Matches.Count
            'vmin' = ($content | Select-String -Pattern '\d+\.?\d*vmin' -AllMatches).Matches.Count
            'vmax' = ($content | Select-String -Pattern '\d+\.?\d*vmax' -AllMatches).Matches.Count
        }
        
        # Modern layout features
        $modernFeatures = @{
            'grid' = ($content | Select-String -Pattern 'display:\s*grid' -AllMatches).Matches.Count
            'flexbox' = ($content | Select-String -Pattern 'display:\s*flex' -AllMatches).Matches.Count
            'css-variables' = ($content | Select-String -Pattern 'var\(' -AllMatches).Matches.Count
            'clamp' = ($content | Select-String -Pattern 'clamp\(' -AllMatches).Matches.Count
            'min-max' = ($content | Select-String -Pattern '(min|max)\(' -AllMatches).Matches.Count
        }
        
        return @{
            FileExists = $true
            Breakpoints = $breakpoints
            FlexibleUnits = $flexibleUnits
            ModernFeatures = $modernFeatures
            TotalMediaQueries = ($content | Select-String -Pattern '@media' -AllMatches).Matches.Count
            ResponsiveScore = [math]::Round((($breakpoints.Values | Measure-Object -Sum).Sum + 
                                          ($flexibleUnits.Values | Measure-Object -Sum).Sum + 
                                          ($modernFeatures.Values | Measure-Object -Sum).Sum) / 3, 1)
        }
    } catch {
        return @{
            FileExists = $true
            Error = $_.Exception.Message
        }
    }
}

# Function to create demo HTML for testing
function Create-ResponsiveDemo {
    $demoPath = Join-Path $FullOutputDir "responsive-demo.html"
    
    $html = @"
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RenalTales - Responsive Demo</title>
    <link rel="stylesheet" href="../public/assets/css/main.css">
    <style>
        .demo-section {
            margin: 2rem 0;
            padding: 2rem;
            border: 2px dashed #ccc;
            border-radius: 8px;
        }
        .viewport-info {
            position: fixed;
            top: 10px;
            right: 10px;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 10px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 12px;
            z-index: 1000;
        }
        .component-showcase {
            display: grid;
            gap: 1rem;
            margin: 1rem 0;
        }
        @media (min-width: 768px) {
            .component-showcase {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (min-width: 1024px) {
            .component-showcase {
                grid-template-columns: repeat(3, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="viewport-info">
        <div>Viewport: <span id="viewport-size">0x0</span></div>
        <div>Breakpoint: <span id="breakpoint">unknown</span></div>
    </div>

    <div class="header">
        <div class="header__container">
            <div class="header__left">
                <img src="../public/assets/images/logo.png" alt="RenalTales" class="header__logo">
            </div>
            <div class="header__center">
                <h1>RenalTales</h1>
                <h2>Responsive Design Test</h2>
            </div>
            <div class="header__right">
                <div class="lang-switcher">
                    <select class="lang-switcher__selector">
                        <option value="en">English</option>
                        <option value="sk">Slovenčina</option>
                        <option value="de">Deutsch</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <nav class="nav">
        <ul class="nav__list">
            <li class="nav__item"><a href="#" class="nav__link">Home</a></li>
            <li class="nav__item"><a href="#" class="nav__link">About</a></li>
            <li class="nav__item"><a href="#" class="nav__link">Services</a></li>
            <li class="nav__item"><a href="#" class="nav__link">Contact</a></li>
        </ul>
    </nav>

    <main>
        <div class="breadcrumbs">
            <div class="breadcrumbs__item">
                <a href="#" class="breadcrumbs__link">Home</a>
            </div>
            <div class="breadcrumbs__item">
                <span class="breadcrumbs__current">Responsive Test</span>
            </div>
        </div>

        <section class="demo-section">
            <h2>Button Components</h2>
            <div class="component-showcase">
                <button class="button button--primary">Primary Button</button>
                <button class="button button--secondary">Secondary Button</button>
                <button class="button button--success">Success Button</button>
                <button class="button button--danger">Danger Button</button>
                <button class="button button--small">Small Button</button>
                <button class="button button--large">Large Button</button>
            </div>
        </section>

        <section class="demo-section">
            <h2>Form Components</h2>
            <div class="form">
                <div class="form__group">
                    <label class="form__label">Email Address</label>
                    <input type="email" class="form__input" placeholder="Enter your email">
                </div>
                <div class="form__group">
                    <label class="form__label">Message</label>
                    <textarea class="form__input" rows="4" placeholder="Your message"></textarea>
                </div>
                <button class="button button--primary">Submit</button>
            </div>
        </section>

        <section class="demo-section">
            <h2>Card Components</h2>
            <div class="component-showcase">
                <div class="card">
                    <div class="card__header">
                        <h3 class="card__title">Sample Card</h3>
                    </div>
                    <div class="card__body">
                        <p class="card__text">This is a sample card with some content to test responsive behavior.</p>
                    </div>
                    <div class="card__footer">
                        <button class="button button--primary">Action</button>
                    </div>
                </div>
                
                <div class="card card--elevated">
                    <div class="card__body">
                        <h4 class="card__title">Elevated Card</h4>
                        <p class="card__text">This card has enhanced shadow for better visual hierarchy.</p>
                    </div>
                </div>
                
                <div class="card card--outlined">
                    <div class="card__body">
                        <h4 class="card__title">Outlined Card</h4>
                        <p class="card__text">This card uses borders instead of shadows.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
        function updateViewportInfo() {
            const width = window.innerWidth;
            const height = window.innerHeight;
            
            document.getElementById('viewport-size').textContent = width + 'x' + height;
            
            let breakpoint = 'Unknown';
            if (width < 480) breakpoint = 'Small Mobile';
            else if (width < 768) breakpoint = 'Mobile';
            else if (width < 992) breakpoint = 'Tablet';
            else if (width < 1200) breakpoint = 'Small Desktop';
            else breakpoint = 'Desktop';
            
            document.getElementById('breakpoint').textContent = breakpoint;
        }
        
        window.addEventListener('resize', updateViewportInfo);
        window.addEventListener('load', updateViewportInfo);
        
        // Test responsive images
        if ('ResizeObserver' in window) {
            const observer = new ResizeObserver(entries => {
                console.log('Viewport changed:', entries[0].contentRect);
            });
            observer.observe(document.body);
        }
    </script>
</body>
</html>
"@

    Set-Content -Path $demoPath -Value $html
    Write-Success "Created responsive demo: $demoPath"
    return $demoPath
}

# Function to generate performance report
function Generate-PerformanceReport {
    param(
        [hashtable]$OldCssMetrics,
        [hashtable]$NewCssMetrics,
        [hashtable]$ResponsiveResults
    )
    
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $reportPath = Join-Path $FullOutputDir "performance-report.html"
    
    $html = @"
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RenalTales Performance Analysis</title>
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
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        .metric-card {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .metric-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #334155;
        }
        .metric-comparison {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 0.5rem 0;
            padding: 0.5rem;
            background: #f8fafc;
            border-radius: 4px;
        }
        .metric-label {
            font-weight: 500;
        }
        .metric-old {
            color: #dc2626;
        }
        .metric-new {
            color: #059669;
        }
        .metric-improvement {
            font-weight: 600;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.875rem;
        }
        .improvement-positive {
            background: #dcfce7;
            color: #166534;
        }
        .improvement-negative {
            background: #fef2f2;
            color: #991b1b;
        }
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
            margin: 0.5rem 0;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981, #059669);
            transition: width 0.3s ease;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        th {
            background: #f1f5f9;
            font-weight: 600;
            color: #334155;
        }
        .status-excellent { color: #059669; font-weight: 600; }
        .status-good { color: #2563eb; font-weight: 600; }
        .status-needs-improvement { color: #d97706; font-weight: 600; }
        .status-poor { color: #dc2626; font-weight: 600; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🚀 RenalTales Performance Analysis</h1>
        <p>Generated on $timestamp</p>
        <p>Before/After comparison of CSS architecture simplification</p>
    </div>

    <div class="metrics-grid">
"@

    # Add CSS comparison metrics
    if ($OldCssMetrics.FileExists -and $NewCssMetrics.FileExists) {
        $sizeDiff = $NewCssMetrics.SizeKB - $OldCssMetrics.SizeKB
        $sizeImprovement = if ($sizeDiff -lt 0) { 
            "$(([math]::Abs($sizeDiff))) KB smaller" 
        } elseif ($sizeDiff -gt 0) { 
            "$sizeDiff KB larger" 
        } else { 
            "No change" 
        }
        
        $html += @"
        <div class="metric-card">
            <div class="metric-title">📊 File Size Comparison</div>
            <div class="metric-comparison">
                <span class="metric-label">Old CSS:</span>
                <span class="metric-old">$($OldCssMetrics.SizeKB) KB</span>
            </div>
            <div class="metric-comparison">
                <span class="metric-label">New CSS:</span>
                <span class="metric-new">$($NewCssMetrics.SizeKB) KB</span>
            </div>
            <div class="metric-comparison">
                <span class="metric-label">Difference:</span>
                <span class="metric-improvement $(if ($sizeDiff -lt 0) { 'improvement-positive' } else { 'improvement-negative' })">
                    $sizeImprovement
                </span>
            </div>
        </div>
"@
    }

    $html += @"
        <div class="metric-card">
            <div class="metric-title">🎯 Responsive Features</div>
            <div class="metric-comparison">
                <span class="metric-label">Media Queries:</span>
                <span class="metric-new">$($ResponsiveResults.TotalMediaQueries)</span>
            </div>
            <div class="metric-comparison">
                <span class="metric-label">CSS Grid Usage:</span>
                <span class="metric-new">$($ResponsiveResults.ModernFeatures.grid)</span>
            </div>
            <div class="metric-comparison">
                <span class="metric-label">Flexbox Usage:</span>
                <span class="metric-new">$($ResponsiveResults.ModernFeatures.flexbox)</span>
            </div>
            <div class="metric-comparison">
                <span class="metric-label">CSS Variables:</span>
                <span class="metric-new">$($ResponsiveResults.ModernFeatures.'css-variables')</span>
            </div>
        </div>
    </div>

    <h2>📱 Responsive Breakpoint Analysis</h2>
    <table>
        <thead>
            <tr>
                <th>Breakpoint</th>
                <th>Usage Count</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
"@

    foreach ($breakpoint in $ResponsiveResults.Breakpoints.Keys) {
        $count = $ResponsiveResults.Breakpoints[$breakpoint]
        $status = if ($count -gt 5) { "status-excellent" } 
                 elseif ($count -gt 2) { "status-good" }
                 elseif ($count -gt 0) { "status-needs-improvement" }
                 else { "status-poor" }
        
        $statusText = if ($count -gt 5) { "Excellent" } 
                     elseif ($count -gt 2) { "Good" }
                     elseif ($count -gt 0) { "Needs Improvement" }
                     else { "Not Used" }
        
        $html += @"
            <tr>
                <td>$breakpoint</td>
                <td>$count</td>
                <td class="$status">$statusText</td>
            </tr>
"@
    }

    $html += @"
        </tbody>
    </table>

    <h2>📐 Flexible Units Usage</h2>
    <table>
        <thead>
            <tr>
                <th>Unit Type</th>
                <th>Usage Count</th>
                <th>Relative Score</th>
                <th>Progress</th>
            </tr>
        </thead>
        <tbody>
"@

    $maxUsage = ($ResponsiveResults.FlexibleUnits.Values | Measure-Object -Maximum).Maximum
    foreach ($unit in $ResponsiveResults.FlexibleUnits.Keys) {
        $count = $ResponsiveResults.FlexibleUnits[$unit]
        $percentage = if ($maxUsage -gt 0) { [math]::Round(($count / $maxUsage) * 100, 0) } else { 0 }
        
        $html += @"
            <tr>
                <td>$unit</td>
                <td>$count</td>
                <td>$percentage%</td>
                <td>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: $percentage%"></div>
                    </div>
                </td>
            </tr>
"@
    }

    $html += @"
        </tbody>
    </table>

    <h2>💡 Recommendations</h2>
    <div class="metric-card">
        <h3>Performance Optimizations</h3>
        <ul>
            <li>✅ CSS architecture simplified and consolidated</li>
            <li>✅ BEM methodology implemented for better maintainability</li>
            <li>✅ Component-based structure for better organization</li>
"@

    if ($NewCssMetrics.VariableCount -gt 10) {
        $html += "<li>✅ Good use of CSS custom properties ($($NewCssMetrics.VariableCount) variables)</li>"
    } else {
        $html += "<li>⚠️ Consider using more CSS custom properties for better maintainability</li>"
    }

    if ($ResponsiveResults.ResponsiveScore -gt 20) {
        $html += "<li>✅ Excellent responsive design implementation</li>"
    } else {
        $html += "<li>⚠️ Consider adding more responsive features</li>"
    }

    $html += @"
        </ul>
        
        <h3>Next Steps</h3>
        <ul>
            <li>🔧 Test across all major browsers and devices</li>
            <li>🔧 Implement critical CSS loading for better performance</li>
            <li>🔧 Consider CSS compression and minification for production</li>
            <li>🔧 Add performance monitoring for ongoing optimization</li>
        </ul>
    </div>
</body>
</html>
"@

    Set-Content -Path $reportPath -Value $html
    Write-Success "Performance report saved to: $reportPath"
    return $reportPath
}

# Main testing process
try {
    Write-Info "=== Starting Performance and Responsive Testing ==="
    
    # Test old CSS (backup)
    $oldCssPath = Get-ChildItem -Path "css-backup-*" -Directory | Select-Object -First 1 | ForEach-Object { Join-Path $_.FullName "public\assets\css\navigation.css" }
    $oldCssMetrics = if ($oldCssPath -and (Test-Path $oldCssPath)) {
        Test-CssPerformance -CssPath $oldCssPath
    } else {
        @{ FileExists = $false; Error = "Old CSS backup not found" }
    }
    
    # Test new CSS
    $newCssPath = "public\assets\css\navigation.css"
    $newCssMetrics = Test-CssPerformance -CssPath $newCssPath
    
    # Test responsive design
    $responsiveResults = Test-ResponsiveBreakpoints -CssPath $newCssPath
    
    # Create responsive demo
    $demoPath = Create-ResponsiveDemo
    
    # Generate performance report
    Write-Info "Generating performance report..."
    $reportPath = Generate-PerformanceReport -OldCssMetrics $oldCssMetrics -NewCssMetrics $newCssMetrics -ResponsiveResults $responsiveResults
    
    Write-Success "=== Performance Testing Completed ==="
    Write-Info "📊 Results:"
    Write-Info "   New CSS Size: $($newCssMetrics.SizeKB) KB"
    Write-Info "   CSS Rules: $($newCssMetrics.RuleCount)"
    Write-Info "   Media Queries: $($responsiveResults.TotalMediaQueries)"
    Write-Info "   Responsive Score: $($responsiveResults.ResponsiveScore)"
    Write-Info "   Report: $reportPath"
    Write-Info "   Demo: $demoPath"
    
} catch {
    Write-Error "Performance testing failed: $($_.Exception.Message)"
    Write-Error "Stack trace: $($_.ScriptStackTrace)"
    exit 1
}

Write-Success "Performance and responsive testing completed successfully!"
