# Alur Bisnis HIS (Hospital Information System)

Diagram alur end-to-end sistem: dari pendaftaran pasien hingga laporan & audit.

```mermaid
flowchart TD
    subgraph P["1. Registrasi & Antrian"]
        A["Pasien datang"] --> B["Daftar pasien<br/>(/patients/create)<br/>dapat No. RM"]
        B --> C["Buat janji temu<br/>(/appointments/create)<br/>generate No. Antrian<br/>status: waiting"]
        C --> D["Pasien cek antrian<br/>via /cek-antrian (public)"]
        D --> E["TV Display:<br/>/queue-display (poli)<br/>/lab · /pharmacy"]
    end

    subgraph Q["2. Alur Status Antrian"]
        E --> F["checked_in<br/>(daftar ulang di poli)"]
        F --> G["in_progress<br/>(dipanggil dokter)"]
        G --> H["completed / cancelled"]
    end

    subgraph R["3. Pemeriksaan"]
        G --> I["Perawat isi tanda vital<br/>(/vital-sign)"]
        I --> J["Dokter buat Rekam Medis<br/>(draft → final)"]
        J --> K["Output EMR:"]
        K --> K1["Resep"]
        K --> K2["Permintaan Lab"]
        K --> K3["Surat Rujukan / Sakit"]
        K --> K4["Cetak PDF EMR"]
    end

    subgraph L["4. Laboratorium"]
        K2 --> L1["Permintaan lab<br/>status: pending (bisa urgent)"]
        L1 --> L2["Proses sampel<br/>status: in_progress"]
        L2 --> L3["Input hasil per item<br/>(normal/abnormal)"]
        L3 --> L4["completed + notifikasi<br/>pasien & dokter"]
        L4 --> L5["Cetak PDF hasil lab"]
    end

    subgraph F["5. Farmasi & Obat"]
        K1 --> F1["Resep masuk antrean<br/>(/pharmacy/pending,<br/>is_dispensed = false)"]
        F1 --> F2["Apoteker serahkan obat<br/>(dispense)"]
        F2 --> F3["Stok berkurang otomatis<br/>+ catat mutasi"]
        F3 --> F4["Stok batch & expiry<br/>store / adjust / retur"]
        F4 --> F5["Alert: low stock,<br/>reorder, expiring"]
    end

    subgraph B["6. Pembayaran"]
        H --> B1["Buat tagihan<br/>(/billings/create/appointment)<br/>status: unpaid"]
        B1 --> B2{"Bayar?"}
        B2 -- "cash/card/qris/bpjs/insurance" --> B3["Metode campuran<br/>(multi-payment)"]
        B3 --> B4{"Nominal?"}
        B4 -- "kurang dari total" --> B5["partial<br/>(pembayaran sebagian)"]
        B4 -- "sama dengan total" --> B6["paid (lunas)"]
        B6 --> B7["Cetak struk (receipt)"]
        B2 -- "belum" --> B8["tetap unpaid"]
    end

    subgraph M["7. Laporan & Monitoring"]
        B6 --> M1["Laporan harian<br/>(/billing/daily-report)"]
        M1 --> M2["Rekonsiliasi kas per shift"]
        B5 --> M1
        M1 --> M3["/reports (PDF/CSV)"]
        M3 --> M4["Dashboard & Audit trail"]
        L4 --> M3
        F5 --> M3
    end

    H --> M4
    style P fill:#e3f2fd,stroke:#1976d2
    style Q fill:#fff3e0,stroke:#f57c00
    style R fill:#f3e5f5,stroke:#8e24aa
    style L fill:#e8f5e9,stroke:#388e3c
    style F fill:#fff8e1,stroke:#f9a825
    style B fill:#fce4ec,stroke:#c2185b
    style M fill:#efebe9,stroke:#6d4c41
```

## Alur Singkat

1. **Registrasi** — pasien didaftarkan (No. RM), janji temu dibuat dengan No. Antrian otomatis per poli.
2. **Antrian** — pasien dipanggil: `waiting → checked_in → in_progress → completed/cancelled`, ditampilkan di TV display.
3. **Pemeriksaan** — perawat isi tanda vital, dokter buat EMR (draft→final) + resep/lab/rujukan/surat sakit.
4. **Laboratorium** — permintaan `pending → in_progress → completed`, hasil per item (normal/abnormal) + notifikasi.
5. **Farmasi** — resep menunggu diserahkan (dispense), stok & mutasi otomatis berkurang, alert stok menipis/kedaluwarsa.
6. **Pembayaran** — tagihan dibuat, pembayaran bisa sebagian/penuh dengan metode campuran (cash/card/qris/bpjs/insurance).
7. **Laporan** — laporan harian, rekonsiliasi kas, `/reports`, dashboard, dan audit trail.