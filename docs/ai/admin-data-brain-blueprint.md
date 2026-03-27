# Admin AI Data Brain Blueprint

## Tujuan
Membuat Admin AI Chat memahami domain bisnis aplikasi, resource operasional, package, transaksi, dan keuangan tanpa memberi akses liar ke seluruh database.

## Prinsip Arsitektur
1. AI memahami domain bisnis, bukan hanya nama tabel.
2. AI membaca data melalui service internal yang aman.
3. Data sensitif dibatasi dengan whitelist tabel dan kolom.
4. Session memory dipakai untuk follow-up aware chat.
5. Analytics ringkas lebih diutamakan daripada query mentah berulang.

## Domain yang Dicakup
1. Booking & Orders
2. Resources
3. Packages
4. Finance
5. AI Audit

## Fondasi Fase 1
1. `config/ai.php`
   Peta domain, whitelist tabel, whitelist kolom, dan definisi entity.
2. `App\Support\AI\AdminAIDomainRegistry`
   Registry pusat untuk domain metadata.
3. `App\Services\AI\EntityResolverService`
   Resolver nama resource/package dari bahasa natural.
4. `App\Services\AI\ResourceInsightService`
   Snapshot resource dan detail entity resource.
5. `App\Services\AI\PackageInsightService`
   Overview package dan detail package.
6. `App\Services\AI\FinanceInsightService`
   Overview revenue, cost, profit, expense, dan cash movement.

## Fondasi Fase 2
1. Orchestrator tool calling per intent.
2. Snapshot analytics harian:
   - daily sales
   - package profitability
   - resource utilization
   - refund summary
   - operating expense summary
3. Session summary memory untuk sesi chat panjang.
4. Capability prompts yang menyesuaikan domain aktif.

## Guardrail
1. AI hanya boleh baca tabel yang ada di whitelist.
2. AI tidak boleh baca secret, token, atau payment metadata sensitif.
3. Query berat jangka panjang dipindah ke analytics snapshot.
4. Semua jawaban AI admin tetap dicatat ke `ai_chat_logs`.

## Hasil yang Ditargetkan
1. Admin bisa bertanya tentang resource tertentu, package tertentu, atau keuangan tanpa AI menebak-nebak.
2. Follow-up chat tetap nyambung dalam satu sesi.
3. Fondasi siap ditingkatkan ke orchestrator yang lebih cerdas tanpa bongkar ulang struktur.
