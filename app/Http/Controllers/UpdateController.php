<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    private function parseGitLog($command)
    {
        exec("cd " . escapeshellarg(base_path()) . " && " . $command, $output, $returnVar);
        if ($returnVar !== 0) {
            return [];
        }

        $commits = [];
        foreach ($output as $line) {
            $parts = explode('|', $line, 4);
            if (count($parts) >= 4) {
                $commits[] = [
                    'hash' => $parts[0],
                    'date' => $parts[1],
                    'message' => $parts[2],
                    'author' => $parts[3]
                ];
            }
        }
        return $commits;
    }

    public function index(Request $request)
    {
        // Jalankan fetch untuk memastikan kita mendapat status remote terbaru
        exec("cd " . escapeshellarg(base_path()) . " && git fetch origin main 2>&1", $fetchOutput, $fetchStatus);

        $gitFormat = '--pretty=format:"%H|%cd|%s|%an" --date=format:"%d/%m/%Y, %H.%M.%S"';
        
        $perPage = $request->input('per_page', 10);
        $limit = $perPage === 'all' ? '' : "-n " . (int)$perPage;
        
        // Commits jarak jauh (yang ada di Github)
        $remoteCommits = $this->parseGitLog("git log {$limit} {$gitFormat} origin/main");

        // Cek jumlah pembaruan baru yang belum ditarik
        exec("cd " . escapeshellarg(base_path()) . " && git rev-list --count HEAD..origin/main 2>&1", $countOutput, $countReturn);
        $newCommitsCount = ($countReturn === 0 && isset($countOutput[0])) ? (int) $countOutput[0] : 0;

        // Waktu penarikan (fetch) terakhir
        $fetchHeadPath = base_path('.git/FETCH_HEAD');
        $lastFetch = 'Belum pernah';
        if (file_exists($fetchHeadPath)) {
            $lastFetch = date('d/m/Y H:i:s', filemtime($fetchHeadPath));
        }

        // Catatan rilis statis untuk tab Aplikasi
        $appUpdates = [
            [
                'version' => 'Versi 1.1.0 (Pembaruan Sistem Perbankan)',
                'changes' => [
                    '[Pembaruan] Modul Layanan Teller terintegrasi untuk Setor dan Tarik Tunai.',
                    '[Pembaruan] Fitur cetak Struk/Nota transaksi otomatis (Mendukung Printer Thermal).',
                    '[Pembaruan] Dashboard analitik keuangan perbankan secara realtime.',
                    '[Perbaikan] Penyesuaian antarmuka untuk pencatatan Buku Kas Umum.'
                ]
            ],
            [
                'version' => 'Versi 1.0.0 (Rilis Awal Bank Mini)',
                'changes' => [
                    '[Pembaruan] Rilis awal Sistem Informasi Bank Mini Sekolah.',
                    '[Pembaruan] Fitur manajemen master data: Kelas, Jurusan, Nasabah (Siswa/Umum).',
                    '[Pembaruan] Sistem login dan otentikasi petugas/teller.',
                    '[Pembaruan] Fitur Backup Database dan Pembaruan Otomatis.'
                ]
            ]
        ];

        return Inertia::render('Updates', [
            'appUpdates' => $appUpdates,
            'remoteCommits' => $remoteCommits,
            'newCommitsCount' => $newCommitsCount,
            'lastFetch' => $lastFetch,
            'filters' => ['per_page' => $perPage]
        ]);
    }

    public function pull()
    {
        exec("cd " . escapeshellarg(base_path()) . " && git pull origin main 2>&1", $output, $returnVar);
        
        if ($returnVar !== 0) {
            return back()->with('error', 'Gagal menarik pembaruan: ' . implode(" ", $output));
        }

        return back()->with('message', 'Pembaruan berhasil ditarik dari Github!');
    }
}
