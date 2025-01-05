<?php

namespace App\Http\Controllers;

use App\Models\SuratKeluar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SuratKeluarController extends Controller
{
    // Tampilkan semua surat keluar
    public function index(Request $request)
    {
        $query = SuratKeluar::query();

        // Pencarian berdasarkan no_surat, tujuan, atau perihal
        if ($search = $request->get('search')) {
            $query->where('no_surat', 'like', "%$search%")
                  ->orWhere('tujuan', 'like', "%$search%")
                  ->orWhere('perihal', 'like', "%$search%");
        }

        // Menampilkan hasil pencarian dengan pagination
        $suratKeluar = $query->paginate(10);

        return view('surat-keluar.index', compact('suratKeluar'));
    }

    // Tampilkan form tambah surat keluar
    public function create()
    {
        return view('surat-keluar.create');
    }

    public function store(Request $request)
    {
        // Validasi inputan
        $validated = $request->validate([
            'no_surat' => 'required|unique:surat_keluar',
            'tanggal_surat' => 'required|date',
            'tanggal_terima' => 'required|date',
            'tujuan' => 'required|string',
            'perihal' => 'required|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:2048', // Validasi file
        ]);
    
        // Menyimpan file jika ada
        if ($request->hasFile('file')) {
            // Simpan file ke storage/app/public/surat-keluar_files
            $path = $request->file('file')->store('surat-keluar_files', 'public');
            $validated['file'] = $path;
        }
    
        // Simpan data surat keluar
        SuratKeluar::create($validated);
    
        return redirect()->route('surat-keluar.index')->with('success', 'Surat keluar berhasil ditambahkan.');
    }
    
    

    // Tampilkan detail surat keluar
    public function show($id)
    {
        // Temukan surat keluar berdasarkan ID
        $suratKeluar = SuratKeluar::findOrFail($id);
    
        // Kembalikan ke view dengan data surat keluar
        return view('surat-keluar.show', compact('suratKeluar'));
    }
    

    // Tampilkan form edit surat keluar
    public function edit($id)
    {
        $suratKeluar = SuratKeluar::findOrFail($id);
        return view('surat-keluar.edit', compact('suratKeluar'));
    }

    // Update data surat keluar
    public function update(Request $request, $id)
    {
        // Validasi inputan dari pengguna
        $validated = $request->validate([
            'no_surat' => 'required|unique:surat_keluar,no_surat,' . $id,
            'tanggal_surat' => 'required|date',
            'tanggal_terima' => 'required|date',
            'tujuan' => 'required|string',
            'perihal' => 'required|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        ]);

        // Menemukan surat keluar berdasarkan ID
        $suratKeluar = SuratKeluar::findOrFail($id);

        // Proses file jika ada
        if ($request->hasFile('file')) {
            // Hapus file lama jika ada
            if ($suratKeluar->file) {
                Storage::disk('public')->delete($suratKeluar->file);
            }
            // Simpan file baru
            $validated['file'] = $request->file('file')->store('surat-keluar_files', 'public');
        }

        // Update data surat keluar
        $suratKeluar->update($validated); 

        // Redirect ke halaman index surat keluar dengan pesan sukses
        return redirect()->route('surat-keluar.index')->with('success', 'Surat keluar berhasil diperbarui.');
    }
    

    // Hapus data surat keluar
    public function destroy($id)
    {
        // Temukan surat keluar berdasarkan ID
        $suratKeluar = SuratKeluar::findOrFail($id);
    
        // Hapus file surat jika ada
        if ($suratKeluar->file) {
            Storage::disk('public')->delete($suratKeluar->file);
        }
    
        // Hapus data surat keluar
        $suratKeluar->delete();
    
        // Redirect ke halaman index surat keluar dengan pesan sukses
        return redirect()->route('surat-keluar.index')->with('success', 'Surat keluar berhasil dihapus.');
    }

    public function download($id)
{
    // Find the SuratKeluar by ID
    $suratKeluar = SuratKeluar::findOrFail($id);

    // Check if the file exists in storage
    if ($suratKeluar->file && Storage::disk('public')->exists($suratKeluar->file)) {
        // Get the path to the file
        $filePath = storage_path('app/public/' . $suratKeluar->file);

        // Return the file as a download response
        return response()->download($filePath);
    }

    // If file doesn't exist, return a 404 error
    return abort(404, 'File not found.');
}

 
}
