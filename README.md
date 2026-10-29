# Delete_unused_images for PrestaShop

Ten skrypt PHP przeszukuje katalogi z obrazami w sklepie internetowym opartym na PrestaShop, lokalizuje obrazy, które nie są przypisane do żadnego produktu i wyświetla je lub **usuwa**, w zależności od ustawionego trybu.

## Wymagania

- Prestashop (testowane na 1.7)

## Instalacja

Skopiuj plik `foton.php` na swój serwer www (rekomeduję do katalogu głównego swojego sklepu PrestaShop).

## Konfiguracja

1. --Opcjonalnie-- Ustaw ścieżkę do katalogu głównego sklepu:
    ```php
    $shop_root = $_SERVER['DOCUMENT_ROOT'] . '/';
    ```

2. Ustaw limit wyświetlanych obrazów (limit jest po to aby nie zapchać zasobów przy słabych serwerach):
    ```php
    $limit = 1000;
    ```

3. Ustaw tryb działania skryptu (domyslnie jest 0 - tylko wyświetlanie):
    - `0` - tylko wyświetlanie obrazów
    - `1` - usuwanie obrazów (wymaga dozwolonego adresu IP)
    ```php
    $mode = 0;
    ```

    W `$allowed_delete_ip` wpisz swój publiczny adres IP jako tekst, np. `$allowed_delete_ip = '203.0.113.10';`. Zastąp przykład swoim adresem. Niepoprawny lub pusty wpis blokuje kasowanie. Skrypt porównuje go z `$_SERVER['REMOTE_ADDR']` i nie ufa nagłówkowi `X-Forwarded-For`. Po wejściu z dozwolonego IP trzeba jeszcze potwierdzić kasowanie przyciskiem wysyłającym POST.

## Użycie

1. Otwórz skrypt w przeglądarce internetowej np `https://twój_sklep.pl/foton.php`.

2. Skrypt przeszuka katalogi z obrazami (`/img/p/1` do `/img/p/9`), wyświetli tylko te które nie są przypisane do żadnego produktu. W przypadku wybrania `$mode = 1; `obrazy te zostaną usunięte.

3. Na końcu wyświetli całkowity rozmiar przetworzonych obrazów.


## Autor

- Tadeusz Sasnal
- tadeksasnal [at] gmail.com

## Licencja

Ten projekt jest licencjonowany na warunkach licencji MIT - zobacz plik LICENSE aby uzyskać więcej informacji.
