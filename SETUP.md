# Setup Guide

Bu dokümantasyon, projenin çalışır hale getirilmesi için adım adım talimatları içerir.

## Ön Gereksinimler

- Docker & Docker Compose
- Make (opsiyonel, kolaylık için)
- Git

## Kurulum Adımları

### 1. Docker Container'ları Başlatın

```bash
cd /Users/alperenuzun/.claude-worktrees/ai-assisted-dev-teams-presentation/wonderful-rosalind

# Container'ları başlat
make up
# veya
docker-compose up -d
```

### 2. Composer Bağımlılıklarını Yükleyin

```bash
# Composer install
make composer ARGS=install
# veya
docker exec blog-php composer install
```

### 3. JWT Anahtarlarını Oluşturun

```bash
docker exec blog-php php bin/console lexik:jwt:generate-keypair
```

### 4. Veritabanını Oluşturun

```bash
docker exec blog-php php bin/console doctrine:database:create
```

### 5. Tabloları Oluşturun

Doctrine mapping'lerden direkt olarak tablo oluşturalım:

```bash
docker exec blog-php php bin/console doctrine:schema:create
```

### 6. Test Verilerini Yükleyin

```bash
docker exec blog-php php bin/console doctrine:fixtures:load --no-interaction
```

## Doğrulama

### API Endpoint'lerini Test Edin

#### 1. Dashboard Stats (Admin)

```bash
curl http://localhost:8080/admin/dashboard
```

Beklenen Response:
```json
{
  "posts_count": 3,
  "users_count": 2,
  "published_posts": 2,
  "draft_posts": 1
}
```

#### 2. Home Page (Web)

```bash
curl http://localhost:8080/
```

#### 3. Login (API)

```bash
curl -X POST http://localhost:8080/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@blog.com","password":"password"}'
```

Token'ı kaydedin!

#### 4. List Posts (API)

```bash
# TOKEN değişkenini yukarıdaki login'den aldığınız token ile değiştirin
curl http://localhost:8080/api/posts \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

#### 5. Create Post (API)

```bash
curl -X POST http://localhost:8080/api/posts \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{"title":"New Post via API","content":"This is a test post created via the API endpoint"}'
```

#### 6. Register User (API)

```bash
curl -X POST http://localhost:8080/api/register \
  -H "Content-Type: application/json" \
  -d '{"email":"newuser@blog.com","password":"password123"}'
```

## Test'leri Çalıştırın

```bash
# Unit test'leri çalıştır
docker exec blog-php vendor/bin/pest tests/Unit

# Tüm test'leri çalıştır
docker exec blog-php vendor/bin/pest
```

## Sorun Giderme

### Container'lar ayağa kalkmıyorsa

```bash
# Log'ları kontrol edin
docker-compose logs -f php
docker-compose logs -f nginx
docker-compose logs -f postgres

# Container'ları yeniden build edin
docker-compose down
docker-compose up -d --build
```

### Composer hatası alıyorsanız

```bash
# Composer cache'ini temizleyin
docker exec blog-php composer clear-cache

# Tekrar install edin
docker exec blog-php composer install
```

### Veritabanı hatası alıyorsanız

```bash
# Veritabanını sıfırlayın
docker exec blog-php php bin/console doctrine:database:drop --force --if-exists
docker exec blog-php php bin/console doctrine:database:create
docker exec blog-php php bin/console doctrine:schema:create
docker exec blog-php php bin/console doctrine:fixtures:load --no-interaction
```

### Permission hatası alıyorsanız

```bash
# PHP container içindeki var/ klasörü için izinleri düzeltin
docker exec blog-php chmod -R 777 var/
```

## Başarılı Kurulum Kontrol Listesi

- [ ] Docker container'ları çalışıyor (`docker ps` ile kontrol edin)
- [ ] Composer bağımlılıkları yüklendi
- [ ] JWT anahtarları oluşturuldu (`config/jwt/*.pem` dosyaları var)
- [ ] Veritabanı oluşturuldu
- [ ] Tablolar oluşturuldu
- [ ] Fixture'lar yüklendi (2 user, 3 post)
- [ ] Admin dashboard endpoint çalışıyor
- [ ] Home page endpoint çalışıyor
- [ ] API login çalışıyor ve token dönüyor
- [ ] API posts endpoint'leri çalışıyor
- [ ] Unit test'ler geçiyor

## Hızlı Komutlar

```bash
# Tüm setup'ı tek seferde yap (experimental)
make up && \
docker exec blog-php composer install && \
docker exec blog-php php bin/console lexik:jwt:generate-keypair && \
docker exec blog-php php bin/console doctrine:database:create && \
docker exec blog-php php bin/console doctrine:schema:create && \
docker exec blog-php php bin/console doctrine:fixtures:load --no-interaction

# Test et
docker exec blog-php vendor/bin/pest

# Endpoint'leri test et
curl http://localhost:8080/
curl http://localhost:8080/admin/dashboard
```

## Önemli Notlar

- **Default kullanıcılar**: `admin@blog.com` ve `user@blog.com` (şifre: `password`)
- **API Port**: 8080
- **Database**: PostgreSQL 16 (port 5432)
- **JWT Token TTL**: 3600 saniye (1 saat)

Başarılar! 🚀
