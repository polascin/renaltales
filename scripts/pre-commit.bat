@echo off
setlocal enabledelayedexpansion

:: Pre-commit hook to check PSR compliance (Windows version)
:: This script will run before each commit to ensure code quality

echo Running pre-commit checks...

:: Flag to track if any checks failed
set CHECKS_FAILED=0

:: Check if composer is available
where composer >nul 2>&1
if %errorlevel% neq 0 (
    echo Error: Composer is not installed or not in PATH
    exit /b 1
)

:: Check if vendor directory exists
if not exist "vendor" (
    echo Installing dependencies...
    composer install --no-dev --optimize-autoloader
)

:: Get list of staged PHP files
for /f "tokens=*" %%i in ('git diff --cached --name-only --diff-filter=ACM') do (
    echo %%i | findstr /i "\.php$" >nul
    if !errorlevel! equ 0 (
        set STAGED_FILES=!STAGED_FILES! %%i
    )
)

if "%STAGED_FILES%"=="" (
    echo No PHP files to check
    exit /b 0
)

echo Checking staged PHP files:
for %%f in (%STAGED_FILES%) do (
    echo   - %%f
)

:: 1. Check PHP syntax
echo.
echo 1. Checking PHP syntax...
set SYNTAX_ERRORS=0
for %%f in (%STAGED_FILES%) do (
    php -l "%%f" >nul 2>&1
    if !errorlevel! neq 0 (
        echo Syntax error in: %%f
        set SYNTAX_ERRORS=1
    )
)

if %SYNTAX_ERRORS% equ 0 (
    echo [32m✓[0m PHP syntax check passed
) else (
    echo [31m✗[0m PHP syntax check failed
    set CHECKS_FAILED=1
)

:: 2. Check PSR-12 compliance with PHP_CodeSniffer
echo.
echo 2. Checking PSR-12 compliance...
composer phpcs --no-interaction >nul 2>&1
set PHPCS_STATUS=%errorlevel%

if %PHPCS_STATUS% equ 0 (
    echo [32m✓[0m PSR-12 compliance check passed
) else (
    echo [31m✗[0m PSR-12 compliance check failed
    echo.
    echo To fix coding standard violations automatically, run:
    echo   composer phpcbf
    set CHECKS_FAILED=1
)

:: 3. Run PHPStan static analysis
echo.
echo 3. Running static analysis...
composer phpstan --no-interaction >nul 2>&1
set PHPSTAN_STATUS=%errorlevel%

if %PHPSTAN_STATUS% equ 0 (
    echo [32m✓[0m Static analysis passed
) else (
    echo [31m✗[0m Static analysis failed
    set CHECKS_FAILED=1
)

:: 4. Check for common issues
echo.
echo 4. Checking for common issues...

:: Check for var_dump, print_r, die, exit
set FORBIDDEN_FUNCTIONS=0
for %%f in (%STAGED_FILES%) do (
    findstr /r /c:"var_dump" /c:"print_r" /c:"die(" /c:"exit(" "%%f" >nul 2>&1
    if !errorlevel! equ 0 (
        echo Forbidden function found in: %%f
        set FORBIDDEN_FUNCTIONS=1
    )
)

if %FORBIDDEN_FUNCTIONS% equ 0 (
    echo [32m✓[0m No forbidden functions found
) else (
    echo [31m✗[0m Forbidden functions found (var_dump, print_r, die, exit)
    set CHECKS_FAILED=1
)

:: Check for TODO/FIXME comments
set TODO_COMMENTS=0
for %%f in (%STAGED_FILES%) do (
    findstr /r /c:"TODO" /c:"FIXME" /c:"XXX" "%%f" >nul 2>&1
    if !errorlevel! equ 0 (
        echo [33m⚠[0m TODO/FIXME comment found in: %%f
        set TODO_COMMENTS=1
    )
)

if %TODO_COMMENTS% equ 1 (
    echo [33m⚠[0m TODO/FIXME comments found - consider addressing before commit
)

:: 5. Check for merge conflict markers
echo.
echo 5. Checking for merge conflict markers...
set MERGE_CONFLICTS=0
for %%f in (%STAGED_FILES%) do (
    findstr /r /c:"<<<<<<<" /c:"=======" /c:">>>>>>>" "%%f" >nul 2>&1
    if !errorlevel! equ 0 (
        echo Merge conflict markers found in: %%f
        set MERGE_CONFLICTS=1
    )
)

if %MERGE_CONFLICTS% equ 0 (
    echo [32m✓[0m No merge conflict markers found
) else (
    echo [31m✗[0m Merge conflict markers found
    set CHECKS_FAILED=1
)

:: Summary
echo.
echo ==========================================
if %CHECKS_FAILED% equ 0 (
    echo [32mAll checks passed! ✓[0m
    echo Commit can proceed.
    exit /b 0
) else (
    echo [31mSome checks failed! ✗[0m
    echo.
    echo Please fix the issues above before committing.
    echo.
    echo Common fixes:
    echo   - Fix syntax errors: Check PHP syntax manually
    echo   - Fix PSR-12 violations: composer phpcbf
    echo   - Fix static analysis: composer phpstan
    echo   - Remove debug functions: Remove var_dump, print_r, etc.
    echo   - Resolve merge conflicts: Edit files manually
    echo.
    echo To skip these checks (not recommended), use:
    echo   git commit --no-verify
    exit /b 1
)
