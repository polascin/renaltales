Refaktorovanie kódu je proces upravovania existujúceho kódu s cieľom zlepšiť jeho kvalitu bez zmeny jeho funkcionality. Je to ako upratovanie a reorganizácia izby - všetko funguje rovnako, ale je to lepšie usporiadané.

## Hlavné ciele refaktorovania:

- **Zlepšenie čitateľnosti** - kód je jednoduchší na pochopenie
- **Odstránenie duplicít** - rovnaký kód sa neopakuje na viacerých miestach
- **Zjednodušenie štruktúry** - komplexné časti sa rozdelí na menšie, zvládnuteľné celky
- **Uľahčenie údržby** - budúce zmeny sú jednoduchšie

## Príklady bežných refaktorovacích techník:

1. **Premenovanie premenných** - z `x` na `pocetZakaznikov`
2. **Extrakcia metód** - dlhý kód rozdelíme do menších funkcií
3. **Odstránenie mŕtveho kódu** - vymazanie nepoužívaných častí
4. **Zjednodušenie podmienok** - komplexné if-else štruktúry prepísať elegantnejšie

## Kedy refaktorovať:

- Pri pridávaní novej funkcionality
- Pri oprave chýb
- Počas code review
- Keď je kód ťažko pochopiteľný

Refaktorovanie je kontinuálny proces, nie jednorazová aktivita. Je to investícia do budúcnosti projektu, ktorá šetrí čas a znižuje počet chýb.

**Refaktorovanie kódu** znamená úpravu (alebo preorganizovanie) existujúceho zdrojového kódu tak, aby bol prehľadnejší, čitateľnejší a udržiavateľnejší, *bez toho, aby sa zmenilo jeho správanie alebo funkcionalita*.

### Prečo sa refaktoruje?

- **Zlepšenie čitateľnosti:** Kód je jednoduchšie pochopiť neskôr pre vás alebo vašich kolegov.
- **Jednoduchšia údržba:** Chyby sa hľadajú ľahšie a nové funkcie sa pridávajú efektívnejšie.
- **Znižovanie duplicity:** Rovnaká logika sa neobjavuje viackrát na rôznych miestach.
- **Optimalizácia štruktúry:** Lepšie rozdelenie do funkcií, tried, modulov.

### Ako sa refaktorizuje v praxi?

- Premenovanie premenných či funkcií na výstižnejšie názvy.
- Vytváranie funkcií z opakovaných častí kódu (napr. “copy-paste” logika sa extrahuje do jednej metódy).
- Rozdelenie veľkých tried na menšie, logicky súvislé triedy.
- Odstraňovanie zbytočného alebo nevyužívaného kódu.
- Zjednodušenie zložitých konštrukcií alebo podmienok.

### Príklad

Nerefaktorovaný kód:

```python
def vypocitaj(a, b):
    if a != 0:   # tu sa stane delenie, ak a nie je nula
        return b / a  
    else:
        return 'Chyba'
```

Refaktorovaný kód:

```python
def vypocitaj_delenie(delenec, delitel):
    if delitel == 0:
        return 'Chyba'
    return delenec / delitel
```
Zmenil sa názov funkcie a premenných, zjednodušila sa podmienka, ale funkcia robí stále to isté.

---

**Zhrnutie:**  
Refaktorovanie je o **zlepšení štruktúry a kvality kódu pri zachovaní jeho funkčnosti**. Je to dôležitá súčasť práce programátora. Pokojne sa opýtaj na konkrétne techniky alebo príklady z praxe!




GitHub Copilot
Here's the refactored index.php with improved structure, security, and maintainability:

Key Improvements Made:
1. Object-Oriented Structure
Encapsulated all functionality in an Application class
Separated concerns into logical methods
Better code organization and maintainability
2. Security Enhancements
CSRF token integration
All output properly HTML-escaped
Safe server variable access
URL encoding for links
Error handling with debug mode
3. Code Quality
Removed duplicate code
Consistent naming conventions
Proper error handling
Better separation of logic and presentation
4. Performance
Output buffering for better performance
Efficient language loading
Reduced redundant operations
5. Maintainability
Clear method responsibilities
Easy to extend functionality
Consistent code structure
Better documentation
6. Features
Integration with secure SessionManager
Proper CSRF protection
Improved language handling
Better error reporting
7. HTML Structure
Semantic HTML5 elements
Proper meta tags
CSRF token in meta tag for JavaScript use
Clean, valid HTML output
This refactored version is more secure, maintainable, and follows PHP best practices while preserving all the original functionality.
