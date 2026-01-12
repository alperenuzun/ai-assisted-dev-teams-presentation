# Add Localization Support

Implement internationalization (i18n) for the specified feature or endpoint.

## Instructions

Feature/endpoint to localize: `$ARGUMENTS`

### Phase 1: Setup (if not already configured)

1. **Check Translation Configuration**:
   - Verify `config/packages/translation.yaml` exists
   - Check for existing translation files in `translations/`

2. **Install Translation Component** (if needed):
   ```bash
   docker exec blog-php composer require symfony/translation
   ```

3. **Configure Default Locale**:
   ```yaml
   # config/packages/translation.yaml
   framework:
     default_locale: en
     translator:
       default_path: '%kernel.project_dir%/translations'
       fallbacks:
         - en
   ```

### Phase 2: Create Translation Files

1. **Create Translation Structure**:
   ```
   translations/
   ├── messages.en.yaml    # English (default)
   ├── messages.tr.yaml    # Turkish
   ├── validators.en.yaml  # Validation messages (EN)
   └── validators.tr.yaml  # Validation messages (TR)
   ```

2. **Add Translations for Feature**:

   For API responses:
   ```yaml
   # messages.en.yaml
   post:
     created: "Post created successfully"
     updated: "Post updated successfully"
     deleted: "Post deleted successfully"
     not_found: "Post not found"

   error:
     validation: "Validation failed"
     unauthorized: "You must be logged in"
     forbidden: "You don't have permission"
   ```

   ```yaml
   # messages.tr.yaml
   post:
     created: "Yazı başarıyla oluşturuldu"
     updated: "Yazı başarıyla güncellendi"
     deleted: "Yazı başarıyla silindi"
     not_found: "Yazı bulunamadı"

   error:
     validation: "Doğrulama başarısız"
     unauthorized: "Giriş yapmalısınız"
     forbidden: "Bu işlem için yetkiniz yok"
   ```

### Phase 3: Implement Locale Detection

1. **Create Locale Listener** (or use Symfony's built-in):
   ```php
   // src/SharedKernel/Infrastructure/EventListener/LocaleListener.php
   ```

2. **Locale Detection Priority**:
   - Query parameter: `?locale=tr`
   - Header: `Accept-Language: tr`
   - User preference (if authenticated)
   - Default locale

### Phase 4: Create Translations Endpoint

1. **Create Query**:
   ```php
   // src/Api/Application/Translation/Query/GetTranslationsQuery.php
   ```

2. **Create Handler**:
   ```php
   // src/Api/Application/Translation/Query/GetTranslationsQueryHandler.php
   ```

3. **Create Controller**:
   ```php
   // src/Api/Infrastructure/Controller/TranslationController.php

   #[Route('/api/translations/{locale}', name: 'api_translations', methods: ['GET'])]
   public function getTranslations(string $locale): JsonResponse
   ```

4. **Response Format**:
   ```json
   {
     "locale": "tr",
     "translations": {
       "post.created": "Yazı başarıyla oluşturuldu",
       "post.updated": "Yazı başarıyla güncellendi",
       ...
     }
   }
   ```

### Phase 5: Update Existing Endpoints

1. **Modify Controllers** to use translator:
   ```php
   public function __construct(
       private TranslatorInterface $translator
   ) {}

   // Usage
   $message = $this->translator->trans('post.created');
   ```

2. **Update Response DTOs** (if applicable):
   - Add localized message fields
   - Support locale parameter

### Phase 6: Testing

1. **Test Translation Loading**:
   ```bash
   docker exec blog-php php bin/console debug:translation en
   docker exec blog-php php bin/console debug:translation tr
   ```

2. **Test Endpoint**:
   ```bash
   curl http://localhost:8081/api/translations/en
   curl http://localhost:8081/api/translations/tr
   ```

3. **Test Localized Responses**:
   ```bash
   curl http://localhost:8081/api/posts -H "Accept-Language: tr"
   curl "http://localhost:8081/api/posts?locale=tr"
   ```

## File Checklist

- [ ] `config/packages/translation.yaml`
- [ ] `translations/messages.en.yaml`
- [ ] `translations/messages.tr.yaml`
- [ ] `translations/validators.en.yaml`
- [ ] `translations/validators.tr.yaml`
- [ ] Translation controller and endpoint
- [ ] Locale detection middleware/listener
- [ ] Updated existing controllers

## Example Usage

```
/add-localization posts
```

This will:
1. Set up translation infrastructure
2. Create translation files for post-related messages
3. Create `/api/translations/{locale}` endpoint
4. Update post controllers to support localization

## Notes

- Always use translation keys, never hardcode strings
- Follow Symfony translation best practices
- Keep translation files organized by domain
- Document new translation keys for translators
