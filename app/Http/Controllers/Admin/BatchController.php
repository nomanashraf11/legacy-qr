<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Batch;
use App\Models\Link;
use App\Models\Order;
use App\Models\User;
use Yajra\DataTables\DataTables;
use ZipArchive;

class BatchController extends Controller
{
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $query = Batch::has('links')->orderBy('number', 'asc');
                
                // Filter by version type if provided and not empty
                if ($request->has('version_type') && $request->version_type !== '' && $request->version_type !== 'all') {
                    $query->where('version_type', $request->version_type);
                }
                
                $batchesWithLinks = $query->get();
                
                return Datatables::of($batchesWithLinks)
                    ->addIndexColumn()
                    ->addColumn('version_type', function ($row) {
                        $badgeClass = $row->version_type === 'christmas' ? 'bg-success' : 'bg-primary';
                        $label = $row->version_type === 'christmas' ? 'Christmas' : 'Memorial';
                        return '<span class="badge ' . $badgeClass . '">' . $label . '</span>';
                    })
                    ->addColumn('qr_codes', function ($row) {
                        if ($row->links->count() > 0) {
                            return  $row->links->count();
                        }
                        return  0;
                    })
                    ->addColumn('zip', function ($row) {
                        if ($row->links->count() > 0) {
                            return '<a title="Download QR Codes" href="' . route('batches.download.qrCode', $row->uuid) . '"><i class="ri-download-2-line fs-3"></i></a>';
                        }
                        return "";
                    })
                    ->addColumn('xlsx', function ($row) {
                        $rou = route('export', $row->uuid);
                        if ($row->links->count() > 0) {
                            return '<a title="Download QR Codes" href="' . $rou . '"><i class="ri-download-2-line fs-3"></i></a>';
                        }
                        return "";
                    })
                    ->rawColumns(['version_type', 'qr_codes', 'zip', 'xlsx'])
                    ->make(true);
            }
            return view('admin.pages.batch.list');
        } catch (\Throwable $th) {
            return redirect(route('admin.home'))->with(['status' => false, 'message' => 'Something went wrong']);
        }
    }
    public function downloadQRCodes($uuid)
    {
        try {
            $batch = Batch::where('uuid', $uuid)->first();

            $links = $batch->links;

            $zipFileName = 'qr_codes_batch_' . $batch->number . '.zip';
            $zipFilePath = storage_path('app/' . $zipFileName);
            $zip = new ZipArchive();
            $zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

            foreach ($links as $link) {
                $qrCodePath = public_path('images/qr_codes/' . $link->image);
                $svgContent = file_get_contents($qrCodePath);
                $zip->addFromString($link->uuid . '.svg', $svgContent);
            }

            $zip->close();
            return response()->download($zipFilePath, $zipFileName)->deleteFileAfterSend();
        } catch (\Throwable $th) {
            dd($th->getMessage());
        }
    }
    public function downloadAvailableQRCodes($uuid)
    {
        try {
            $batch = Batch::where('uuid', $uuid)->first();

            $links = $batch->availableLinks();

            $zipFileName = 'qr_codes_batch_' . $batch->number . '.zip';
            $zipFilePath = storage_path('app/' . $zipFileName);
            $zip = new ZipArchive();
            $zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

            foreach ($links as $link) {
                $qrCodePath = public_path('images/qr_codes/' . $link->image);
                $svgContent = file_get_contents($qrCodePath);
                $zip->addFromString($link->uuid . '.svg', $svgContent);
            }

            $zip->close();
            return response()->download($zipFilePath, $zipFileName)->deleteFileAfterSend();
        } catch (\Throwable $th) {
            dd($th->getMessage());
        }
    }
    public function adminDashboard()
    {
        try {
            $reSellers = User::role('re-sellers')->get()->count();
            $orders = Order::all()->count();
            $pendingOrders = Order::where('status', 1)->get()->count();
            $linkedCodes = Link::wherenotnull('local_user_id')->get()->count();
            $qrCodes = Link::all()->count();
            $availableCodes = Link::wherenull('local_user_id')->get()->count();

            return view('admin.pages.adminDashboard', compact('reSellers', 'orders', 'pendingOrders', 'availableCodes', 'qrCodes', 'linkedCodes'));
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
