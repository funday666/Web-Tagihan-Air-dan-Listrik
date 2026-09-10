<section id="view-settings" class="space-y-6 hidden">
    <div>
        <h2 class="text-2xl font-extrabold text-slate-900">Pengaturan Aplikasi & WhatsApp</h2>
        <p class="text-slate-500 text-xs sm:text-sm">Sesuaikan rekening pembayaran, tarif utilitas bawaan, dan format
            pesan WA rujukan.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Owner & Payment Info -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4 lg:col-span-1">
            <h3 class="font-bold text-slate-900 text-base border-b border-slate-100 pb-3 flex items-center gap-2">
                <i class="fa-solid fa-building-columns text-gold-600"></i> Informasi Pembayaran
            </h3>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Nama Usaha / Kos / Pengelola</label>
                <input type="text" id="cfg-owner-name"
                    class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-gold-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Nama Bank / E-Wallet</label>
                <input type="text" id="cfg-bank-name" placeholder="misal: BCA / Mandiri / QRIS"
                    class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-gold-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">No. Rekening / No. HP E-Wallet</label>
                <input type="text" id="cfg-account-no"
                    class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-gold-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Atas Nama (A.n.)</label>
                <input type="text" id="cfg-account-holder"
                    class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-gold-500 focus:outline-none">
            </div>
            <hr class="border-slate-100 my-2">
            <h4 class="font-bold text-xs uppercase tracking-wider text-slate-400">Tarif Default Utilitas</h4>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Air (per m³)</label>
                    <input type="number" id="cfg-rate-water"
                        class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-gold-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Listrik (per kWh)</label>
                    <input type="number" id="cfg-rate-elec"
                        class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-gold-500 focus:outline-none">
                </div>
            </div>
            <button onclick="saveSettings()"
                class="w-full mt-4 bg-gradient-to-r from-gold-500 to-amber-600 hover:from-gold-600 hover:to-amber-700 text-slate-950 font-extrabold py-2.5 rounded-xl text-xs shadow-sm transition">
                Simpan Pengaturan
            </button>
        </div>

        <!-- WhatsApp Template Form -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4 lg:col-span-2">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                    <i class="fa-brands fa-whatsapp text-wa-light text-xl"></i> Templat WhatsApp Notifikasi
                </h3>
                <button onclick="resetWaTemplate()" class="text-xs font-bold text-slate-400 hover:text-slate-600">
                    Reset ke Standard
                </button>
            </div>
            <p class="text-xs text-slate-500">Pesan ini akan terisi secara otomatis dan siap dikirim ke nomor WhatsApp
                rujukan pelanggan. Klik tag berikut untuk menyisipkan variabel:</p>

            <div class="flex flex-wrap gap-1.5 text-xs">
                <span
                    class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-md font-mono cursor-pointer hover:bg-slate-200"
                    onclick="insertTag('{noreg}')">{noreg}</span>
                <span
                    class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-md font-mono cursor-pointer hover:bg-slate-200"
                    onclick="insertTag('{nama}')">{nama}</span>
                <span
                    class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-md font-mono cursor-pointer hover:bg-slate-200"
                    onclick="insertTag('{kamar}')">{kamar}</span>
                <span
                    class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-md font-mono cursor-pointer hover:bg-slate-200"
                    onclick="insertTag('{periode}')">{periode}</span>
                <span
                    class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-md font-mono cursor-pointer hover:bg-slate-200"
                    onclick="insertTag('{sewa}')">{sewa}</span>
                <span
                    class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-md font-mono cursor-pointer hover:bg-slate-200"
                    onclick="insertTag('{air_awal}')">{air_awal}</span>
                <span
                    class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-md font-mono cursor-pointer hover:bg-slate-200"
                    onclick="insertTag('{air_akhir}')">{air_akhir}</span>
                <span
                    class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-md font-mono cursor-pointer hover:bg-slate-200"
                    onclick="insertTag('{air_m3}')">{air_m3}</span>
                <span
                    class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-md font-mono cursor-pointer hover:bg-slate-200"
                    onclick="insertTag('{air}')">{air}</span>
                <span
                    class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-md font-mono cursor-pointer hover:bg-slate-200"
                    onclick="insertTag('{listrik_awal}')">{listrik_awal}</span>
                <span
                    class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-md font-mono cursor-pointer hover:bg-slate-200"
                    onclick="insertTag('{listrik_akhir}')">{listrik_akhir}</span>
                <span
                    class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-md font-mono cursor-pointer hover:bg-slate-200"
                    onclick="insertTag('{listrik_kwh}')">{listrik_kwh}</span>
                <span
                    class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-md font-mono cursor-pointer hover:bg-slate-200"
                    onclick="insertTag('{listrik}')">{listrik}</span>
                <span
                    class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-md font-mono cursor-pointer hover:bg-slate-200"
                    onclick="insertTag('{biaya_lain}')">{biaya_lain}</span>
                <span
                    class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-md font-mono cursor-pointer hover:bg-slate-200"
                    onclick="insertTag('{total_periode}')">{total_periode}</span>
                <span
                    class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-md font-mono cursor-pointer hover:bg-slate-200"
                    onclick="insertTag('{tunggakan}')">{tunggakan}</span>
                <span
                    class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-md font-mono cursor-pointer hover:bg-slate-200"
                    onclick="insertTag('{total}')">{total}</span>
                <span
                    class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-md font-mono cursor-pointer hover:bg-slate-200"
                    onclick="insertTag('{jatuh_tempo}')">{jatuh_tempo}</span>
            </div>

            <div>
                <textarea id="cfg-wa-template" rows="11"
                    class="w-full p-3 font-mono text-xs leading-relaxed border border-slate-200 rounded-xl focus:ring-2 focus:ring-gold-500 focus:outline-none bg-slate-50"></textarea>
            </div>
            <div class="flex justify-end">
                <button onclick="saveSettings()"
                    class="bg-gradient-to-r from-gold-500 to-amber-600 hover:from-gold-600 hover:to-amber-700 text-slate-950 font-extrabold px-6 py-2.5 rounded-xl text-xs shadow-sm transition">
                    Simpan Templat WA
                </button>
            </div>
        </div>
    </div>
</section>
