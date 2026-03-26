# AI Center Blueprint

## Scope
- Feature 3: Auto Package Composer
- Feature 5: Admin AI Chat Query

## Flow Ringkas

### Auto Package Composer
1. Admin pilih objective.
2. Admin set constraints.
3. AI generate 3-5 kandidat paket.
4. Sistem jalankan validation guardrails.
5. Admin approve sebagai draft/publish.

### Admin AI Chat Query
1. Admin kirim pertanyaan natural language.
2. AI deteksi intent.
3. Sistem tarik data relevan.
4. AI jawab dengan angka + rekomendasi.
5. Aksi sensitif wajib konfirmasi dan dicatat.

## Database Tables
- ai_insights
- ai_chat_logs
- ai_action_logs

## Route
- GET /admin/ai-center (name: admin.ai-center.index)