<?php

namespace App\Http\Controllers;

use App\Imports\ExcelImport;
use App\Models\ExcelDetail;
use App\Models\ExcelFile;
use App\Services\ExcelService;
use Exception;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;


class ExcelController extends Controller
{
    protected $excelService;
    public function __construct(ExcelService $excelService)
    {
        $this->excelService = $excelService;
    }

    public function index()
    {
        $excelFiles = ExcelFile::all();
        return view('excel.listing', compact('excelFiles'));
    }

    public function create()
    {
        return view('excel.create');
    }

    public function store(Request $request)
    {
        $response = $this->excelService->storeExcelData($request);
        if ($response['status'] == 'success') {
            return redirect()->route('excel_lisiting')->with($response);
        }
        return redirect()->back()->with($response);
    }

    public function show($excelFileId)
    {
        $response = $this->excelService->showExcelDetail($excelFileId);
        if (isset($response['excelData']) && isset($response['headings'])) {
            return view('excel.details', ['headings' => $response['headings'] ?? null, 'excelData' => $response['excelData'] ?? null]);
        }
        return redirect()->back()->with($response);
    }
}