# 🛳️ Dijital Signage ve Toplantı Odası Yönetim Sistemi

> **İstanbul Deniz Otobüsleri (İDO)** — Bilgi Teknolojileri Birimi  
> Staj Projesi · Ağustos 2026 · Burak Güleryüz

---

## 📋 Proje Hakkında

Şirket içi toplantı odalarının rezervasyon, çakışma denetimi ve anlık doluluk gösteriminin tek bir rol tabanlı sistem üzerinden yönetilmesini sağlayan bir web uygulamasıdır.

**Çözülen problemler:**

- Aynı oda ve saatte birden fazla toplantı ayarlanabiliyordu **(çakışma)**
- Odaların doluluk durumu salonun önüne gelinmeden anlaşılamıyordu
- Arızalı bir odaya toplantı planlanabiliyordu

---

## ✨ Özellikler

| Özellik               | Açıklama                                                                                   |
| --------------------- | ------------------------------------------------------------------------------------------ |
| 🔒 Kimlik Doğrulama    | Kimlik no + 6 haneli şifre ile giriş; bcrypt ile şifreleme; CSRF korumalı formlar          |
| 👋 Karşılama Ekranı    | Girişten sonra aktif oda / bugünkü toplantı özeti ve Odalar-Toplantılar kısayolları        |
| 👥 Rol Yönetimi        | Personel, Stajyer, Bakım Sorumlusu, Özel Kalem Müdürü; yetki kontrolü sunucu tarafında     |
| 🚫 Çakışma Kontrolü    | Aynı oda/saat çakışması otomatik engellenir                                                |
| 📺 Brifing Ekranı      | 60 sn'de bir yenilenen, şifresiz erişilebilir signage ekranı                               |
| 🔧 Arıza Modülü        | Bakım sorumlusu oda arızasını işaretler; arızalı oda tabelada uyarı olarak görünür         |
| 🔴 Acil Öncelik        | Acil toplantılar listede kırmızı rozetle öne çıkar                                         |
| 🏛️ Yönetim Kurulu     | Gizli toplantı tipi; yalnızca Özel Kalem Müdürü görür ve oluşturur; tabelada "Özel Toplantı" yazar |
| 🔑 Otomatik Kimlik No  | Kayıtta 000001, 000002... formatında sıralı numara atanır                                  |

---

## 🛠️ Teknolojiler

- **Backend:** PHP 8.2+ · Laravel 12 · Eloquent ORM
- **Frontend:** Blade · Bootstrap 5 (CDN) · JavaScript · SVG Animasyonlar
- **Veritabanı:** MySQL (XAMPP)
- **Araçlar:** Git · GitHub · Artisan CLI · Composer

---

## 🗄️ Veritabanı Yapısı

| Tablo      | Önemli Alanlar                                                                       |
| ---------- | ------------------------------------------------------------------------------------ |
| `users`    | employee_id (benzersiz), name, email, password (bcrypt), role                        |
| `rooms`    | name, location, capacity, is_active, is_faulty, fault_note                           |
| `meetings` | room_id (rooms'a bağlı), title, organizer, type, priority, start_time, end_time      |

Oturumlar `sessions` tablosunda tutulur (Laravel'in hazır tabloları da migration ile gelir).

---

## 🚀 Kurulum

### Gereksinimler

- PHP 8.2+
- Composer
- MySQL (XAMPP önerilir)
- İnternet bağlantısı (Bootstrap CDN üzerinden yüklenir)

### Adımlar

```
# 1. Repoyu klonla
git clone https://github.com/BurakGuleryuz/Brifing-salonu-sitesi-ido-v2.git
cd Brifing-salonu-sitesi-ido-v2

# 2. Bağımlılıkları kur
composer install

# 3. .env dosyasını oluştur
cp .env.example .env

# 4. Uygulama anahtarı üret
php artisan key:generate
```

`.env` dosyasında veritabanı ayarlarını düzenle:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=brifing_db
DB_USERNAME=root
DB_PASSWORD=
```

```
# 5. phpMyAdmin'den brifing_db adında boş bir veritabanı oluştur

# 6. Ayarları yenile ve tabloları oluştur
php artisan config:clear
php artisan migrate

# 7. Sunucuyu başlat
php artisan serve
```

Uygulama `http://127.0.0.1:8000` adresinde çalışır.

> Aynı bilgisayarda birden fazla Laravel projesi çalıştırıyorsan, çerezlerin karışmaması için `.env` dosyasına projeye özel bir `SESSION_COOKIE=brifing_v2_session` satırı ekle.

---

## 👤 Kullanıcı Oluşturma

`/register` sayfasından kayıt ol: kimlik numarası otomatik atanır (000001, 000002...) ve **herkes `personel` rolüyle başlar**. Kullanıcı kendi rolünü seçemez; yetkili roller aşağıdaki gibi atanır:

```
php artisan tinker
```

```
App\Models\User::where('employee_id', '000001')->update(['role' => 'ozel_kalem_muduru']);
App\Models\User::where('employee_id', '000002')->update(['role' => 'bakim_sorumlusu']);
App\Models\User::where('employee_id', '000003')->update(['role' => 'personel']);
App\Models\User::where('employee_id', '000004')->update(['role' => 'stajyer']);
```

Geçerli rol değerleri: `personel`, `ozel_kalem_muduru`, `bakim_sorumlusu`, `stajyer`.  
**Şifre kuralı:** tam 6 rakam; bir rakam en fazla 2 kez tekrar edebilir (`112233` geçerli, `111234` geçersiz).

---

## 🔐 Rol Yetki Matrisi

| İşlem                                   | Stajyer | Personel | Bakım Sorumlusu | Özel Kalem Müdürü |
| --------------------------------------- | :-----: | :------: | :-------------: | :---------------: |
| Odaları ve toplantıları görüntüleme     |    ✅    |    ✅     |        ✅        |         ✅         |
| Normal toplantı oluşturma               |    ❌    |    ✅     |        ✅        |         ✅         |
| Yönetim Kurulu toplantısı oluşturma/görme |   ❌    |    ❌     |        ❌        |         ✅         |
| Toplantı düzenleme / silme              |    ❌    |    ❌     |        ❌        |         ✅         |
| Oda ekleme / düzenleme / silme          |    ❌    |    ❌     |        ❌        |         ✅         |
| Arıza bildirme / arızayı kapatma        |    ❌    |    ❌     |        ✅        |         ❌         |

> Yetkisiz bir işlem butonu gizlenerek değil, **sunucu tarafında** engellenir: yetkisiz kullanıcı adresi elle yazsa bile `403` hatası alır.

---

## 📺 Brifing (Signage) Ekranı Kullanımı

Her oda için ayrı bir URL vardır:

```
http://127.0.0.1:8000/signage/{oda_id}
```

`{oda_id}` veritabanındaki oda numarasıdır. En kolayı, Odalar sayfasındaki **"Ekranı Aç"** butonudur; doğru adresi kendisi açar.

Bu URL'yi oda kapısındaki TV veya tablette **tam ekran** açık bırakın.

- ✅ Şifre **gerekmez**
- 🔄 60 saniyede bir **otomatik güncellenir**
- 🟢 Müsait / 🔴 Dolu / ⚠️ Arızalı durumunu renklerle gösterir
- Bugünün sonraki toplantılarını listeler
- Yönetim Kurulu toplantılarının başlığı gösterilmez, "Özel Toplantı" yazar

---

## 🏗️ Sistem Mimarisi

```
Tarayıcı
   ↓
Route (routes/web.php)
   ├── Herkese açık: /login, /register, /forgot-password, /signage/{room}
   └── Korumalı (auth.simple): /home, /rooms/*, /meetings/*
         ↓
   Middleware (EnsureUserIsLoggedIn)
         ↓
   ┌──────────────────────────────────┐
   │          Laravel MVC             │
   │  Views ←→ Controllers ←→ Models  │
   └──────────────────────────────────┘
         ↓
   MySQL Veritabanı
      ↙          ↘
Brifing Ekranı   Yönetim Paneli
(şifresiz, TV)   (rol bazlı)
```

---

## 📁 Proje Yapısı

```
Brifing-salonu-sitesi-ido-v2/
├── app/Http/Controllers/
│   ├── Controller.php             # Ortak yetki kontrolleri (rol metodları)
│   ├── Auth/LoginController.php   # Giriş, kayıt, şifre sıfırlama
│   ├── HomeController.php         # Giriş sonrası karşılama ekranı
│   ├── RoomController.php         # Oda CRUD + arıza yönetimi
│   ├── MeetingController.php      # Toplantı CRUD + çakışma kontrolü
│   └── SignageController.php      # Brifing ekranı
├── app/Http/Middleware/
│   └── EnsureUserIsLoggedIn.php   # Oturum kontrolü (auth.simple)
├── app/Models/
│   ├── User.php                   # Kullanıcı (employee_id, role)
│   ├── Room.php                   # Aktif/pasif/arızalı durum
│   └── Meeting.php                # scopeOverlapping() çakışma kontrolü
├── resources/views/
│   ├── auth/                      # Giriş, kayıt, şifre sıfırlama
│   ├── home.blade.php             # Karşılama ekranı
│   ├── rooms/                     # Oda yönetim ekranları
│   ├── meetings/                  # Toplantı yönetim ekranları
│   ├── signage/show.blade.php     # Brifing ekranı (TV için)
│   └── layouts/app.blade.php      # Ortak layout + animasyonlar
├── database/migrations/           # Tablo tanımları
├── docs/                          # Dokümanlar ve ekran görüntüleri
└── routes/web.php
```

---

## 🆕 v2 Sürüm Notları

- **Karşılama ekranı** eklendi: giriş sonrası doğrudan Odalar yerine özet ekran (`HomeController`, `home.blade.php`).
- **Stajyer kısıtı** eklendi: stajyer toplantı oluşturamaz (buton gizlenir, `create`/`store` sunucuda `403` döner).
- **Kod sadeleştirildi:** ortak yetki metodları `Controller.php`'de toplandı; doğrulama kuralları ve çakışma kontrolü tek metoda alındı; `scopeOverlapping` basit `if` yapısına çevrildi.
- **Acil e-posta bildirimi kaldırıldı:** sunucuda mail ayarı olmadığı için mailler gerçekte gitmiyordu ve kayıtlı e-posta adresleri sistemin ürettiği sahte adreslerdi. Acil toplantılar rozetle vurgulanmaya devam eder.
- Rol yetki matrisi ve README gerçek davranışla uyumlu hale getirildi.

---

## ⚠️ Bilinen Sınırlamalar ve Geliştirme Fikirleri

- Giriş denemesi için sınır (throttle) yok; şifre kuralı kolay kullanım için 6 rakamla sınırlı.
- "Şifremi unuttum" kimlik no + ad soyad ile doğrulama yapar; e-posta doğrulaması yoktur.
- Bir oda silinirse o odaya ait toplantılar da silinir (`cascade`).
- E-posta bildirimi için gerçek e-posta alanı ve SMTP ayarı gerekir.
- Otomatik test (PHPUnit) yazılmadı; testler elle yapıldı.

---

## 🔧 Sık Karşılaşılan Sorunlar

| Belirti                              | Çözüm                                                                                                                   |
| ------------------------------------ | ----------------------------------------------------------------------------------------------------------------------- |
| `Could not open input file: artisan` | `artisan` dosyasının olduğu klasörde olduğundan emin ol (`dir` ile kontrol et).                                          |
| `419 Page Expired`                   | `.env` içine projeye özel `SESSION_COOKIE` ekle, `php artisan config:clear` çalıştır, tarayıcı çerezlerini sil.         |
| `Unknown database`                   | phpMyAdmin'de veritabanını oluştur; adı `.env` içindeki `DB_DATABASE` ile birebir aynı olsun.                           |
| Sayfa stilsiz görünüyor              | Bootstrap CDN'den yüklenir; internet bağlantısı gerekir.                                                                |

---

## 📸 Ekran Görüntüleri

### Giriş Ekranı

![Giriş Ekranı](docs/screenshots/login.png)

### Odalar Yönetim Paneli

![Odalar](docs/screenshots/rooms.png)

### Toplantılar Listesi

![Toplantılar](docs/screenshots/meetings.png)

### Brifing Ekranı

![Signage](docs/screenshots/signage.png)

---

## 📄 Lisans

Bu proje İDO (İstanbul Deniz Otobüsleri) bünyesinde staj kapsamında geliştirilmiştir.

---

Geliştirici: [Burak Güleryüz](https://github.com/BurakGuleryuz) · Bilgisayar Mühendisliği Stajyeri · 2026