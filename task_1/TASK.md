# Zadanie rekrutacyjne: Azure Search API Client

## Poziom: Senior PHP Developer

## Cel zadania

Twoim zadaniem jest przeanalizowanie i rozwinięcie istniejącej implementacji klienta Azure Search API poprzez identyfikację i naprawę celowo wprowadzonych błędów.

## Kontekst

System synchronizuje katalog produktów z Azure AI Search. Klient jest używany do zarządzania indeksami oraz batchowego dodawania i usuwania dokumentów. Występują problemy z niezawodnością przy dużych operacjach i sporadyczne błędy przy komunikacji z API.

## Warunki wejściowe

### API Azure Search - odpowiedzi

**Rate limiting (HTTP 429):**
```json
{
    "error": {
        "code": "429",
        "message": "retry after 5 seconds"
    }
}
```

**Ograniczenia Azure Search:**
- Maksymalnie 32000 dokumentów w jednym requestcie
- Timeout domyślny: 60 sekund dla indeksowania

## Co otrzymujesz

1. **`AbstractAzureClient.php`** - Abstrakcyjna klasa bazowa do wykonywania requestów HTTP
2. **`AzureSearchIndexClient.php`** - Implementacja klienta Azure Search Index
3. **`SearchIndexClientInterface.php`** - Interfejs
4. **`Product.php`** - Encja produktu

## Wymagania funkcjonalne

1. **Klient musi obsługiwać odpowiedzi z rate limitingiem** - system powinien automatycznie powtarzać requesty
2. **Operacje na dużych zbiorach danych** - obsługa usuwania tysięcy dokumentów
3. **Idempotentność** - wielokrotne wywołanie tej samej operacji powinno dawać ten sam efekt
4. **Poprawna obsługa błędów** - różne kody HTTP wymagają różnej reakcji

## Oczekiwany rezultat

Działający, niezawodny kod który:
- Poprawnie obsługuje komunikację z Azure Search API
- Radzi sobie z dużymi wolumenami danych
- Jest odporny na chwilowe problemy sieciowe (retry logic)

## Co oceniamy

1. **Poprawność rozwiązań** - czy kod działa w edge case'ach
2. **Wydajność i skalowalność** - czy rozwiązania są optymalne dla dużych danych
3. **Czystość kodu** - czytelność, SOLID, PSR
4. **PHP 8.x features** - wykorzystanie nowoczesnych funkcji języka
5. **programming** - czy kandydat myśli o błędach i anomaliach

## Zadania dodatkowe (dla chętnych)

1. Jak przetestowałbyś tę implementację?
2. Jak dodałbyś observability (logging, metryki)?
3. Jak obsłużyłbyś timeouty przy bardzo dużych operacjach?
4. Co zrobiłbyś inaczej, gdybyś projektował tę funkcjonalność od zera?
