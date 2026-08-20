# Alur Bisnis HIS (Hospital Information System)

Diagram alur end-to-end sistem: dari pendaftaran pasien hingga laporan & audit, mencakup seluruh modul (rawat inap, IGD, operasi, lab, radiologi, farmasi, purchasing, keuangan, BPJS, imunisasi, stock opname, surat kematian, dan antrian online).

```mermaid
flowchart TD
    subgraph P["1. Registrasi & Antrian"]
        A["Pasien datang"] --> P1["Daftar pasien<br/>(/patients/create)<br/>dapat No. RM"]
        P1 --> C["Buat janji temu<br/>(/appointments/create)<br/>No. Antrian: Q{poliCode}-{Ymd}-{seq}<br/>status: waiting"]
        C --> D["TV Display:<br/>/queue-display (poli)<br/>/lab · /pharmacy"]
    end

    subgraph PO["2. Antrian Online (Portal Publik)"]
        PO1["Pasien pesan via portal<br/>(/portal) tanpa login"] --> PO2["No. registrasi: AQ-{Ymd}-{seq}<br/>status: registered"]
        PO2 --> PO3["Check-in di loket<br/>status: checked_in"]
        PO3 --> PO4["Selesai dilayani<br/>status: completed"]
        PO2 -. batal .-> PO5["cancelled"]
    end

    subgraph Q["3. Alur Status Antrian"]
        D --> Q1["checked_in<br/>(daftar ulang di poli)"]
        PO3 --> Q1
        Q1 --> G["in_progress<br/>(dipanggil dokter)"]
        G --> H["completed / cancelled"]
    end

    subgraph R["4. Pemeriksaan"]
        G --> I["Perawat isi tanda vital<br/>(/vital-sign)"]
        I --> J["Dokter buat Rekam Medis<br/>(draft → final)"]
        J --> K["Output EMR:"]
        K --> K1["Resep"]
        K --> K2["Permintaan Lab"]
        K --> K3["Permintaan Radiologi"]
        K --> K4["Surat Rujukan / Sakit"]
        K --> K5["Cetak PDF EMR"]
        J --> IM1
    end

    subgraph IM["5. Imunisasi"]
        IM1["Catat imunisasi<br/>(/immunizations/create)<br/>13 jenis vaksin"] --> IM2["Validasi tanggal<br/>berikutnya & lokasi"]
        IM2 --> IM3["Riwayat imunisasi pasien"]
    end

    subgraph L["6. Laboratorium"]
        K2 --> L1["Permintaan lab<br/>status: pending (bisa urgent)"]
        L1 --> L2["Proses sampel<br/>status: in_progress"]
        L2 --> L3["Input hasil per item<br/>(normal/abnormal)"]
        L3 --> L4["completed + notifikasi<br/>pasien & dokter"]
        L4 --> L5["Cetak PDF hasil lab"]
    end

    subgraph RAD["7. Radiologi"]
        K3 --> R1["Permintaan radiologi<br/>status: pending<br/>+ item per tes"]
        R1 --> R2["Proses pemeriksaan<br/>status: in_progress"]
        R2 --> R3["Input hasil per item<br/>findings · impression<br/>(normal/abnormal)"]
        R3 --> R4["completed + notifikasi<br/>dokter & kasir"]
        R4 --> R5["Cetak PDF hasil radiologi"]
    end

    subgraph F["8. Farmasi & Obat"]
        K1 --> F1["Resep masuk antrean<br/>(/pharmacy/pending)<br/>is_dispensed = false"]
        F1 --> F2["Apoteker serahkan obat<br/>(dispense)"]
        F2 --> F3["Stok berkurang otomatis<br/>+ catat mutasi"]
        F3 --> F4["Stok batch & expiry<br/>store / adjust / retur"]
        F4 --> F5["Alert: low stock,<br/>reorder, expiring"]
    end

    subgraph OP["9. Stock Opname"]
        OP1["Buat opname<br/>draft<br/>(stok sistem vs aktual)"] --> OP2["Approve apoteker<br/>status: approved"]
        OP2 --> OP3["Sesuaikan stok batch<br/>+ mutasi in/out<br/>ref: opname#{no}"]
    end

    subgraph PU["10. Purchasing"]
        PU1["Buat PO / Supplier<br/>draft (qty × harga)"] --> PU2["Kirim pesanan<br/>status: ordered"]
        PU2 --> PU3["Terima barang<br/>status: received"]
        PU3 --> PU4["Stok bertambah otomatis<br/>batch PO-{id}<br/>+ mutasi in<br/>+ notifikasi apoteker"]
        PU2 -. batalkan .-> PU5["cancelled"]
    end

    subgraph IGD["11. IGD & Triase"]
        E1["Pasien gawat datang"] --> E2["Registrasi IGD<br/>(/emergency/create)<br/>triase: red/yellow/<br/>green/black<br/>status: waiting"]
        E2 --> E3["Triase perawat<br/>status: in_triage"]
        E3 --> E4["Penanganan<br/>status: treatment"]
        E4 --> E5["Observasi<br/>status: observation"]
        E5 --> E6["Selesai:"]
        E6 --> E6A["Pulang<br/>discharged"]
        E6 --> E6B["Rujuk<br/>referred"]
        E6 --> E6C["Rawat Inap<br/>admitted"]
        E6 --> E6D["Meninggal<br/>deceased"]
    end

    subgraph RI["12. Rawat Inap"]
        RI1["Pasien masuk rawat inap<br/>(/inpatient-admissions/create)<br/>pilih kamar + tempat tidur<br/>status: admitted"] --> RI2["Cek kamar / tempat tidur<br/>(/rooms · /beds)"]
        RI2 --> RI3["Pulang / Discharge<br/>status: discharged<br/>bed otomatis kosong"]
    end

    subgraph SUR["13. Operasi & Bedah"]
        S1["Jadwal operasi<br/>(/surgeries/create)<br/>status: scheduled<br/>prosedur + ICD-9"] --> S2["Mulai operasi<br/>status: in_progress"]
        S2 --> S3["Selesai<br/>status: completed"]
        S2 -. batal .-> S4["cancelled"]
    end

    subgraph B["14. Pembayaran & Keuangan"]
        H --> B1["Buat tagihan<br/>(/billings/create/appointment)<br/>status: unpaid"]
        B1 --> B2{"Bayar?"}
        B2 -- "cash/card/qris/bpjs/insurance" --> B3["Metode campuran<br/>(multi-payment)"]
        B3 --> B4{"Nominal?"}
        B4 -- "kurang dari total" --> B5["partial<br/>(pembayaran sebagian)"]
        B4 -- "sama dengan total" --> B6["paid (lunas)"]
        B6 --> B7["Cetak struk (receipt)"]
        B2 -- "belum" --> B8["tetap unpaid"]
        B6 --> BX["Kelebihan bayar?<br/>Refund (/refunds/create)<br/>kurangi paid_amount"]
        B1 --> BY["Catat pengeluaran<br/>(/expenses)<br/>nomor EXP-..."]
    end

    subgraph BP["15. BPJS (SEP & Klaim)"]
        BP1["Buat SEP<br/>status: aktif<br/>rawat jalan / rawat inap"] --> BP2["Batal SEP<br/>status: dibatalkan"]
        BP3["Ajukan Klaim<br/>status: diajukan"] --> BP4["Update status<br/>disetujui (nilai approve)<br/>/ ditolak / menunggu"]
    end

    subgraph SK["16. Surat Kematian"]
        SK2["Buat Surat Kematian<br/>(/death-certificates/create)<br/>nomor SK-..."] --> SK3["Cetak / download<br/>surat (printable)"]
    end

    subgraph M["17. Laporan & Monitoring"]
        B6 --> M1["Laporan harian<br/>(/billing/daily-report)"]
        M1 --> M2["Rekonsiliasi kas per shift"]
        B5 --> M1
        M1 --> M3["/reports (PDF/CSV)"]
        M3 --> M4["Dashboard & Audit trail"]
        L4 --> M3
        F5 --> M3
    end

    E6C --> RI1
    E6D --> SK2
    RI3 --> B1
    E6A --> B1
    E6B --> B1
    H --> M4

    style P fill:#e3f2fd,stroke:#1976d2
    style PO fill:#e0f7fa,stroke:#0097a7
    style Q fill:#fff3e0,stroke:#f57c00
    style R fill:#f3e5f5,stroke:#8e24aa
    style IM fill:#ede7f6,stroke:#5e35b1
    style L fill:#e8f5e9,stroke:#388e3c
    style RAD fill:#dcedc8,stroke:#689f38
    style F fill:#fff8e1,stroke:#f9a825
    style OP fill:#f1f8e9,stroke:#7cb342
    style PU fill:#fff8e1,stroke:#ffb300
    style IGD fill:#ffebee,stroke:#d32f2f
    style RI fill:#fce4ec,stroke:#c2185b
    style SUR fill:#ef9a9a,stroke:#c62828
    style B fill:#fce4ec,stroke:#c2185b
    style BP fill:#e8eaf6,stroke:#3949ab
    style SK fill:#eceff1,stroke:#546e7a
    style M fill:#efebe9,stroke:#6d4c41
```

## Alur Singkat

1. **Registrasi** — pasien didaftarkan (No. RM), janji temu dibuat dengan No. Antrian otomatis per poli (`Q{poliCode}-{Ymd}-{seq}`).
2. **Antrian Online** — pasien memesan via portal publik (tanpa login), mendapat No. registrasi `AQ-{Ymd}-{seq}` (4 digit), lalu check-in di loket (`registered → checked_in → completed/cancelled`).
3. **Antrian** — pasien dipanggil: `waiting → checked_in → in_progress → completed/cancelled`, ditampilkan di TV display.
4. **Pemeriksaan** — perawat isi tanda vital, dokter buat EMR (draft→final) + resep/lab/radiologi/rujukan/surat sakit.
5. **Imunisasi** — perawat mencatat imunisasi (13 jenis vaksin) dengan validasi tanggal berikutnya.
6. **Laboratorium** — permintaan `pending → in_progress → completed`, hasil per item (normal/abnormal) + notifikasi.
7. **Radiologi** — permintaan `pending → in_progress → completed`, hasil per item (findings/impression) + notifikasi.
8. **Farmasi** — resep menunggu diserahkan (dispense), stok & mutasi otomatis berkurang, alert stok menipis/kedaluwarsa.
9. **Stock Opname** — opname `draft → approved`; saat approve stok batch disesuaikan + mutasi dicatat.
10. **Purchasing** — PO `draft → ordered → received/cancelled`; saat receive stok bertambah otomatis (batch `PO-{id}`).
11. **IGD & Triase** — `waiting → in_triage → treatment → observation` lalu `discharged / referred / admitted / deceased` (triase red/yellow/green/black).
12. **Rawat Inap** — pasien masuk (`admitted`) pilih kamar/bed, lalu `discharged` (bed otomatis kosong).
13. **Operasi & Bedah** — jadwal `scheduled → in_progress → completed/cancelled` (prosedur + ICD-9).
14. **Pembayaran** — tagihan dibuat, pembayaran bisa sebagian/penuh (multi-payment), plus refund bila kelebihan bayar dan pencatatan pengeluaran.
15. **BPJS** — SEP `aktif → dibatalkan`; Klaim `diajukan → disetujui/ditolak/menunggu`.
16. **Surat Kematian** — dibuat saat pasien meninggal (IGD/rawat inap), bisa dicetak/diunduh.
17. **Laporan** — laporan harian, rekonsiliasi kas, `/reports`, dashboard, dan audit trail.

## Status per Modul

| Modul | Status |
|---|---|
| Antrian (Appointment) | `waiting → checked_in → in_progress → completed / cancelled` |
| Antrian Online | `registered → checked_in → completed / cancelled` |
| Rekam Medis | `draft → finalized` |
| Laboratorium | `pending → in_progress → completed / cancelled` |
| Radiologi | `pending → in_progress → completed / cancelled` |
| Farmasi (resep) | dispense: `is_dispensed false → true` |
| Stock Opname | `draft → approved` |
| Purchase Order | `draft → ordered → received / cancelled` |
| Rawat Inap | `admitted → discharged / cancelled` |
| IGD & Triase | `waiting → in_triage → treatment → observation → discharged / referred / admitted / deceased` |
| Operasi | `scheduled → in_progress → completed / cancelled` |
| Tagihan (Billing) | `unpaid → partial → paid / cancelled` |
| SEP BPJS | `aktif → dibatalkan` |
| Klaim BPJS | `diajukan → disetujui / ditolak / menunggu` |