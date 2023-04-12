<?php

namespace App\Services;

use App\Models\ExcelDetail;
use App\Models\ExcelFile;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ExcelService
{
    public function storeExcelData($request)
    {
        try {
            DB::beginTransaction();
            $file = $request->file('file')->getClientOriginalName();
            $filename = pathinfo($file, PATHINFO_FILENAME);
            $extension = pathinfo($file, PATHINFO_EXTENSION);

            $excelFile =  new ExcelFile;
            $excelFile->name = $filename . '.' . $extension;
            $excelFile->user_id = auth()->user()->id ?? 1;

            if ($excelFile->save()) {
                $excelData = Excel::toArray(null, $request->file('file'))[0];
                $heading = $excelData[0];
                $arrayForInsertion = array();
                array_shift($excelData);

                foreach ($excelData as $key => $data) {
                    foreach ($data as $key2 => $detail) {
                        $arrayForInsertion[$key][$key2]['excel_file_id'] = $excelFile->id;
                        $arrayForInsertion[$key][$key2]['heading'] = $heading[$key2];
                        $arrayForInsertion[$key][$key2]['value'] = $detail;
                        $arrayForInsertion[$key][$key2]['row_number'] = $key + 1;
                    }
                }
                foreach ($arrayForInsertion as $key => $record) {
                    ExcelDetail::insert($record);
                }
            }
            DB::commit();
            return ['status' => 'success', 'message' => 'Excel sheet Successfully inserted'];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return ['status' => 'error', 'message' => 'An error occured. Please try again'];
        }
    }

    public function showExcelDetail($excelFileId)
    {
        $excelDetails = ExcelDetail::where('excel_file_id', $excelFileId)->get();
        $headings = array();
        foreach ($excelDetails as $detail) {
            if ($detail->row_number == 1) {
                array_push($headings, ucfirst($detail->heading));
            } else {
                break;
            }
        }

        $rowNumber = 1;
        $counter = 0;
        $valueCounter = 0;
        $excelData = array();
        foreach ($excelDetails as $key => $value) {
            if ($rowNumber == $value->row_number) {
                $excelData[$counter][$valueCounter] = $value->value;
            } else {
                $rowNumber = $value->row_number;
                $counter++;
                $valueCounter = 0;
                $excelData[$counter][$valueCounter] = $value->value;
            }
            $valueCounter++;
        }
        if ($headings && $excelData) {
            return ['headings' => $headings, 'excelData' => $excelData];
        }
        return ['status' => 'error', 'message' => 'Data not found'];
    }
}
