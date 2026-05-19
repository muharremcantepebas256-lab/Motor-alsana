# Motor Alsana (PHP + MySQL)

Profesyonel motosiklet ilan platformu:
- Detayli filtreleme sistemi (11 filtre)
- Modern ve responsive arayuz
- Ilan ekleme, duzenleme, silme
- Kullanici kayit/giris
- Favori ekleme ve fiyat takibi
- Fiyat dusunce e-posta bildirimi

## Ozellikler

### Filtreleme Sistemi
- **Marka** - 30+ marka secenegi
- **Model** - Serbest metin arama
- **Model Yili** - Min/Max aralik
- **Kilometre** - Min/Max aralik
- **Fiyat Araligi** - Min/Max TL
- **Silindir Hacmi (CC)** - Min/Max aralik
- **Beygir Gucu (HP)** - Min/Max aralik
- **Motorsiklet Tipi** - 14 farkli tip
- **Silindir Sayisi** - 1, 2, 3, 4, 6
- **Renk** - 18 renk secenegi
- **Mensei / Uretim Ulkesi** - 13 ulke

### Tasarim
- Premium modern arayuz
- Sticky sidebar filtreleme (masaustu)
- Mobil slide-in filtre paneli
- Hover efektleri ve animasyonlar
- Aktif filtre etiketleri
- Responsive tasarim (masaustu, tablet, mobil)

## Dosya ve klasorler

- `config/db.php`: MySQL baglantisi.
- `config/auth.php`: Session ve giris kontrol fonksiyonlari.
- `config/motor_types.php`: Marka, tip, renk, ulke listeleri.
- `config/mailer.php`: E-posta gonderim fonksiyonu.
- `index.php`: Ana sayfa, filtreleme sistemi ve ilan listesi.
- `add_listing.php`: Admin ilan ekleme sayfasi.
- `edit_listing.php`: Admin ilan duzenleme sayfasi.
- `listing.php`: Ilan detay sayfasi.
- `favorite.php`: Favoriye ekleme islemi.
- `unfavorite.php`: Favoriden cikarma islemi.
- `my_favorites.php`: Kullanicinin favori ilanlari.
- `register.php`: Kullanici kayit.
- `login.php`: Kullanici giris.
- `logout.php`: Cikis.
- `check_prices.php`: Fiyat dususu kontrolu.
- `delete_listing.php`: Ilan silme islemi.
- `schema.sql`: Veritabani tablolari.
- `uploads/`: Yuklenen fotograf dosyalari.
- `assets/style.css`: Modern arayuz stilleri.

## Adim adim kurulum

1. XAMPP'te `Apache` ve `MySQL` servislerini baslat.
2. `phpMyAdmin` ac (`http://localhost/phpmyadmin`).
3. `schema.sql` dosyasini calistir.
4. Gerekirse `config/db.php` icindeki veritabani bilgilerini guncelle:
   - host: `127.0.0.1`
   - user: `root`
   - pass: ``
   - db: `motor_alsana`
5. Tarayicida su adrese git:
   - `http://localhost/Motor%20alsana/`
6. Once kayit ol, sonra giris yap.
7. `Motor Ekle` sayfasindan ilan olustur.

### Mevcut veritabanini guncelleme

Eger mevcut bir veritabaniniz varsa, `schema.sql` icerisindeki yorum satirlarindaki `ALTER TABLE` komutlarini calistirarak yeni kolonlari ekleyin.

## Fiyat dususu bildirim mantigi

1. Kullanici bir ilani favoriye ekledigi anda fiyat `favorites.last_price` olarak kaydedilir.
2. `check_prices.php` calisinca fiyat karsilastirilir, dusmus ise e-posta gonderilir.

## Notlar

- Tum sorgular `prepared statement` ile SQL injection'a karsi korunmustur.
- Input kontrol ve validasyon tum formlarda mevcuttur.
- Veritabani indeksleri filtreleme performansi icin eklenmistir.
