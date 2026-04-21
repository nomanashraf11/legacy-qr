<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddLinkRequest;
use App\Models\Batch;
use App\Models\Link;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use App\Models\LocalUser;
use App\Models\Photo;
use App\Models\Tribute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;
use ZipArchive;
use Maatwebsite\Excel\Excel;
use App\Exports\LinksExport;



class QrCodeController extends Controller
{
    /**
     * Absolute SPA base URL embedded in QR codes (scheme + host, no trailing slash).
     * Requires CLIENT_URL in .env and a fresh config cache.
     */
    protected function clientProfileBaseUrl(): string
    {
        $base = rtrim((string) config('app.client_url'), '/');
        if ($base !== '') {
            return $base;
        }

        throw new \RuntimeException(
            'CLIENT_URL is not set or is empty. Add CLIENT_URL=https://your-spa-host to .env, then run: php artisan config:clear && php artisan config:cache'
        );
    }

    // public function index(Request $request)
    // {
    //     try {
    //         if ($request->ajax()) {
    //             $data = Link::where('local_user_id', null)->get();
    //             return Datatables::of($data)
    //                 ->addIndexColumn()
    //                 ->addColumn('qr_code', function ($row) {
    //                     if ($row->image) {
    //                         return '<img height="100" width="100" src="' . asset('images/qr_codes/' . $row->image) . '" alt="">';
    //                     }
    //                     return "";
    //                 })
    //                 ->addColumn('status', function ($row) {
    //                     if ($row->is_sold == 0) {
    //                         return  '<span class="badge bg-success">Available</span>';
    //                     }
    //                     return  '<span class="badge bg-danger">Sold out</span>';
    //                 })
    //                 ->addColumn('link', function ($row) {
    //                     return '<a target="_blank" href="' . config('app.client_url') . '/' . $row->uuid . '" class="">' . config('app.client_url') . '/' . $row->uuid . '</a>';
    //                 })
    //                 ->addColumn('batch', function ($row) {
    //                     if ($row->batch->number) {
    //                         return  $row->batch->number;
    //                     }
    //                     return  null;
    //                 })
    //                 ->addColumn('action', function ($row) {
    //                     return '<a title="Download QR Codes" href="' . route('downloadSvg', $row->image) . '"><i class="ri-download-2-line fs-3"></i></a>';
    //                 })
    //                 ->rawColumns(['qr_code', 'status', 'batch', 'link', 'action'])
    //                 ->make(true);
    //         }
    //         return view('admin.pages.links.list');
    //     } catch (\Throwable $th) {
    //         dd($th->getMessage());
    //         return redirect(route('admin.home'))->with(['status' => false, 'message' => 'Something went wrong']);
    //     }
    // }
    public function available()
    {
        try {
            return view('admin.pages.batch.available');
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $query = Batch::hasAvailableLinks();
                
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
                        return '<span class="badge '.$badgeClass.'">'.$label.'</span>';
                    })
                    ->addColumn('qr_codes', function ($row) {
                        if ($row->availableLinks()->count() > 0) {
                            return $row->availableLinks()->count();
                        }
                        return 0;
                    })
                    ->addColumn('zip', function ($row) {
                        if ($row->availableLinks()->count() > 0) {
                            return '<a title="Download QR Codes" href="'.route('batches.available.download.qrCode', $row->uuid).'"><i class="ri-download-2-line fs-3"></i></a>';
                        }
                        return "";
                    })
                    ->addColumn('xlsx', function ($row) {
                        $rou = route('available.export', $row->uuid);
                        if ($row->availableLinks()->count() > 0) {
                            return '<a title="Download QR Codes" href="'.$rou.'"><i class="ri-download-2-line fs-3"></i></a>';
                        }
                        return "";
                    })
                    ->addColumn('action', function ($row) {
                        $view = route('admin.available.links', $row->uuid);
                        return '<a title="View Details" href="'.$view.'"><i class="mdi mdi-eye fs-3"></i></a>';
                    })
                    ->rawColumns(['version_type', 'qr_codes', 'zip', 'xlsx', 'action'])
                    ->make(true);
            }
            return view('admin.pages.batch.available');
        } catch (\Throwable $th) {
            return redirect(route('admin.home'))->with(['status' => false, 'message' => 'Something went wrong']);
        }
    }
    public function availableLinks($uuid)
    {
        try {
            return view('admin.pages.links.list', compact('uuid'));
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
    public function availableLinksList(Request $request, $uuid)
    {
        try {
            if ($request->ajax()) {
                $batch = Batch::where('uuid', $uuid)->first();
                $query = $batch->links()->whereNull('local_user_id');
                
                // Filter by version type if provided and not empty
                if ($request->has('version_type') && $request->version_type !== '' && $request->version_type !== 'all') {
                    $query->where('version_type', $request->version_type);
                }
                
                $data = $query->get();
                
                return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('qr_code', function ($row) {
                        if ($row->image) {
                            return '<img height="100" width="100" src="'.asset('images/qr_codes/'.$row->image).'" alt="">';
                        }
                        return "";
                    })
                    ->addColumn('status', function ($row) {
                        if ($row->is_sold == 0) {
                            return '<span class="badge bg-success">Available</span>';
                        }
                        return '<span class="badge bg-danger">Sold out</span>';
                    })
                    ->addColumn('version_type', function ($row) {
                        $badgeClass = $row->version_type === 'christmas' ? 'bg-success' : 'bg-primary';
                        $label = $row->version_type === 'christmas' ? 'Christmas' : 'Memorial';
                        return '<span class="badge '.$badgeClass.'">'.$label.'</span>';
                    })
                    ->addColumn('link', function ($row) {
                        try {
                            $base = $this->clientProfileBaseUrl();
                        } catch (\RuntimeException $e) {
                            return '<span class="text-danger" title="'.e($e->getMessage()).'">(configure CLIENT_URL)</span>';
                        }

                        return '<a target="_blank" href="'.$base.'/'.$row->uuid.'" class="">'.$base.'/'.$row->uuid.'</a>';
                    })
                    ->addColumn('batch', function ($row) {
                        return $row->batch?->number ?? '—';
                    })
                    ->addColumn('action', function ($row) {
                        return '<a title="Download QR Codes" href="'.route('downloadSvg', $row->image).'"><i class="ri-download-2-line fs-3"></i></a>';
                    })
                    ->rawColumns(['qr_code', 'status', 'batch', 'link', 'action', 'version_type'])
                    ->make(true);
            }
            return view('admin.pages.links.list');
        } catch (\Throwable $th) {
            dd($th->getMessage());
            return redirect(route('admin.home'))->with(['status' => false, 'message' => 'Something went wrong']);
        }
    }
    public function store(AddLinkRequest $request)
    {
        try {
            $clientBase = $this->clientProfileBaseUrl();
        } catch (\RuntimeException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        try {
            DB::beginTransaction();
            $latestBatch = Batch::latest()->first();
            $newBatch = $latestBatch
                ? Batch::create([
                    'uuid' => Str::uuid(), 
                    'name' => $request->name, 
                    'number' => $latestBatch->number + 1,
                    'version_type' => $request->version_type ?? 'full'
                ])
                : Batch::create([
                    'uuid' => Str::uuid(), 
                    'number' => 1, 
                    'name' => $request->name,
                    'version_type' => $request->version_type ?? 'full'
                ]);

            $links = []; // Initialize an array to hold the links

            for ($i = 0; $i < $request->number; $i++) {
                $link = Link::create([
                    'uuid' => Str::random(12),
                    'batch_id' => $newBatch->id,
                    'version_type' => $request->version_type ?? 'full'
                ]);
                $svgCode = QrCode::size(500)->errorCorrection('H')->style('round')->generate($clientBase.'/'.$link->uuid, public_path('images/qr_codes/'.$link->uuid.'.svg'));

                $link->update([
                    'id' => $link->id,
                    'image' => $link->uuid.'.svg',
                ]);
                $links[] = $link;
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'QR Codes Generated Successfully',
                'data' => $links,
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
            ]);
        }
    }
    public function availableExport($uuid)
    {
        try {
            $clientBase = $this->clientProfileBaseUrl();
        } catch (\RuntimeException $e) {
            abort(422, $e->getMessage());
        }

        try {
            $batch = Batch::where('uuid', $uuid)->first();
            $links = $batch->availableLinks();
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setCellValue('A1', 'Web LINKS');
            $sheet->setCellValue('B1', 'QR CODE IMAGE FILE');

            $sheet->getColumnDimension('A')->setWidth(50); // Adjust width as needed
            $sheet->getColumnDimension('B')->setWidth(30);

            $row = 2;
            foreach ($links as $link) {
                $sheet->setCellValue('A'.$row, $clientBase.'/'.$link['uuid']);
                $sheet->setCellValue('B'.$row, $link['image']);
                $row++;
            }

            $writer = new Xlsx($spreadsheet);
            $filename = 'links.xlsx';
            $excelDir = public_path('excel');
            if (!file_exists($excelDir)) {
                mkdir($excelDir, 0755, true);
            }
            $savePath = $excelDir . '/' . $filename;
            $writer = new Xlsx($spreadsheet);
            $writer->save($savePath);

            return response()->download($savePath)->deleteFileAfterSend(true);
        } catch (\Throwable $th) {
            dd($th->getMessage());
        }
    }
    public function export($uuid)
    {
        try {
            $clientBase = $this->clientProfileBaseUrl();
        } catch (\RuntimeException $e) {
            abort(422, $e->getMessage());
        }

        try {
            $batch = Batch::where('uuid', $uuid)->first();
            $links = $batch->links;
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setCellValue('A1', 'Web LINKS');
            $sheet->setCellValue('B1', 'QR CODE IMAGE FILE');

            $sheet->getColumnDimension('A')->setWidth(50); // Adjust width as needed
            $sheet->getColumnDimension('B')->setWidth(30);

            $row = 2;
            foreach ($links as $link) {
                $sheet->setCellValue('A'.$row, $clientBase.'/'.$link['uuid']);
                $sheet->setCellValue('B'.$row, $link['image']);
                $row++;
            }

            $writer = new Xlsx($spreadsheet);
            $filename = 'links.xlsx';
            $excelDir = public_path('excel');
            if (!file_exists($excelDir)) {
                mkdir($excelDir, 0755, true);
            }
            $savePath = $excelDir . '/' . $filename;
            $writer = new Xlsx($spreadsheet);
            $writer->save($savePath);

            return response()->download($savePath)->deleteFileAfterSend(true);
        } catch (\Throwable $th) {
            dd($th->getMessage());
        }
    }
    private function generateQrCodeWithLogo($uuid)
    {
        $svgCode = QrCode::size(500)->errorCorrection('H')->style('round')->generate($this->clientProfileBaseUrl().'/'.$uuid);

        $dom = new \DOMDocument();
        $dom->loadXML($svgCode);

        $logoPath = public_path('images/logo/logocentered3.png');
        $logo = imagecreatefrompng($logoPath);

        $logoWidth = imagesx($logo);
        $logoHeight = imagesy($logo);
        $x = ($dom->documentElement->getAttribute('width') - $logoWidth) / 2;
        $y = ($dom->documentElement->getAttribute('height') - $logoHeight) / 2;

        $imageElement = $dom->createElement('image');
        $imageElement->setAttribute('x', $x);
        $imageElement->setAttribute('y', $y);
        $imageElement->setAttribute('width', $logoWidth);
        $imageElement->setAttribute('height', $logoHeight);

        $xlinkNS = 'http://www.w3.org/1999/xlink';
        $imageElement->setAttributeNS($xlinkNS, 'xlink:href', 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath)));

        $dom->documentElement->appendChild($imageElement);

        return $dom->saveXML();
    }
    public function show($uuid)
    {
        try {
            $link = Link::where('uuid', $uuid)->firstorfail();
            return view('admin.pages.qr_details', compact('link'));
        } catch (\Throwable $th) {
            dd($th->getMessage());
        }
    }
    public function link_user($uuid)
    {
        try {
            $link = Link::where('uuid', $uuid)->first();
            if ($link->user_id) {
                return 'view profile';
            } else {
                return redirect(route('register'));
            }
        } catch (\Throwable $th) {
            dd('hi');
        }
    }
    public function linkedCodes(Request $request)
    {
        try {
            if ($request->ajax()) {
                $query = Link::whereNotNull('local_user_id');
                
                // Filter by version type if provided and not empty
                if ($request->has('version_type') && $request->version_type !== '' && $request->version_type !== 'all') {
                    $query->where('version_type', $request->version_type);
                }
                
                $data = $query->get();
                
                return Datatables::of($data)
                    ->addIndexColumn()
                    ->editColumn('created_at', function ($row) {
                        return $row->created_at
                            ? '<span data-order="'.$row->created_at->timestamp.'">'.$row->created_at->timezone('America/Chicago')->format('m/d/Y h:i:s A').'</span>'
                            : '-';
                    })
                    ->addColumn('user', function ($row) {
                        return $row->localUser?->user?->email ?? '—';
                    })
                    ->addColumn('version_type', function ($row) {
                        $badgeClass = $row->version_type === 'christmas' ? 'bg-success' : 'bg-primary';
                        $label = $row->version_type === 'christmas' ? 'Christmas' : 'Memorial';
                        return '<span class="badge '.$badgeClass.'">'.$label.'</span>';
                    })
                    ->addColumn('action', function ($row) {
                        $view = route('admin.qr.view', $row->uuid);
                        return '<a title="View Details" href="'.$view.'"><i class="mdi mdi-eye fs-3"></i></a>';
                    })
                    ->addColumn('batch', function ($row) {
                        return $row->batch?->number ?? '—';
                    })
                    ->addColumn('link', function ($row) {
                        try {
                            $base = $this->clientProfileBaseUrl();
                        } catch (\RuntimeException $e) {
                            return '<span class="text-danger" title="'.e($e->getMessage()).'">(configure CLIENT_URL)</span>';
                        }

                        return '<a target="_blank" href="'.$base.'/'.$row->uuid.'" class="">'.$base.'/'.$row->uuid.'</a>';
                    })

                    ->rawColumns(['batch', 'action', 'user', 'link', 'created_at', 'version_type'])
                    ->make(true);
            }
            return view('admin.pages.links.linkedList');
        } catch (\Throwable $th) {
            dd($th->getMessage());
            return redirect(route('admin.home'))->with(['status' => false, 'message' => 'Something went wrong']);
        }
    }
    public function linkByUser($uuid)
    {
        try {
            $localUser = LocalUser::where('uuid', $uuid)->firstorfail();
            $qrCodes = $localUser->links()->paginate(8);
            return view('admin.pages.linksByUser', compact('qrCodes'));
        } catch (\Throwable $th) {
            dd($th->getMessage());
        }
    }
    public function deleteProfile($uuid)
    {
        try {
            DB::beginTransaction();

            $qrCode = Link::where('uuid', $uuid)->firstOrFail();
            if ($qrCode->profile) {
                if ($qrCode->profile->profile_picture) {
                    $filePathToDelete = public_path('images/profile/profile_pictures/'.$qrCode->profile->profile_picture);
                    $this->deletePicture($filePathToDelete);
                }
                if ($qrCode->profile->cover_picture) {
                    $filePathToDelete = public_path('images/profile/cover_pictures/'.$qrCode->profile->cover_picture);
                    $this->deletePicture($filePathToDelete);
                }
                if ($qrCode->profile->relations) {
                    foreach ($qrCode->profile->relations as $relation) {
                        if ($relation->image_name) {
                            $filePathToDelete = public_path('images/profile/relations/'.$relation->image_name);
                            $this->deletePicture($filePathToDelete);
                        }
                    }
                }
                $qrCode->profile->delete();
            }
            if ($qrCode->photos) {
                foreach ($qrCode->photos as $photo) {
                    if ($photo->image) {
                        $filePathToDelete = public_path('images/profile/photos/'.$photo->image);
                        $this->deletePicture($filePathToDelete);
                    }
                }
                $qrCode->photos()->delete();
            }
            if ($qrCode->timelines) {
                $qrCode->timelines()->delete();
            }
            if ($qrCode->tributes) {
                foreach ($qrCode->tributes as $tribute) {
                    if ($tribute->image) {
                        $filePathToDelete = public_path('images/profile/tributes/'.$tribute->image);
                        $this->deletePicture($filePathToDelete);
                    }
                }
                $qrCode->tributes()->delete();
            }

            $qrCode->update([
                'id' => $qrCode->id,
                'local_user_id' => null
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Deleted Successfully'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with([
                'status' => false,
                'message' => 'Something went wrong'
            ]);
        }
    }
    public function deletePhotosFromProfile($uuid)
    {
        try {
            DB::beginTransaction();
            $photo = Photo::where('uuid', $uuid)->firstorfail();
            if ($photo->image) {
                $filePathToDelete = public_path('images/profile/photos/'.$photo->image);
                $this->deletePicture($filePathToDelete);
            }
            $photo->delete();
            DB::commit();
            return redirect()->back()->with([
                'status' => true,
                'message' => 'Deleted Successfully'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with([
                'status' => false,
                'message' => 'Something went wrong'
            ]);
        }
    }
    public function deleteTribute($uuid)
    {
        try {
            DB::beginTransaction();
            $tribute = Tribute::where('uuid', $uuid)->firstorfail();
            if ($tribute->image) {
                $tributeImage = public_path('images/profile/tributes/'.$tribute->image);
                $this->deletePicture($tributeImage);
            }
            $tribute->delete();
            DB::commit();
            return redirect()->back()->with([
                'status' => true,
                'message' => 'Deleted Successfully'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with([
                'status' => false,
                'message' => 'Something went wrong'
            ]);
        }
    }
    public function transferData(Request $request)
    {
        try {
            DB::beginTransaction();
            $old_link = Link::where('uuid', $request->old)->firstorfail();
            $old_local_user_id = $old_link->local_user_id;
            $old_profile = $old_link->profile;

            $new_link = Link::where('uuid', $request->new)->firstorfail();

            if ($new_link->profile || $new_link->local_user_id) {
                return response()->json([
                    'status' => false,
                    'message' => 'New QR Code is Already Linked'
                ]);
            } else {
                $new_link->update([
                    'id' => $new_link->id,
                    'local_user_id' => $old_local_user_id,
                ]);
                $old_profile->update([
                    'id' => $old_profile->id,
                    'link_id' => $new_link->id,
                ]);
                $old_link->delete();
                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'Data Transfered Successfully'
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            dd($th->getMessage());
        }
    }
    public function downloadSvg($image)
    {
        try {
            $filePath = public_path('images/qr_codes/'.$image);
            return response()->download($filePath, 'qrCode.svg');
        } catch (\Throwable $th) {
            dd($th->getMessage());
        }
    }
    private function deletePicture($pictureWithCompletePath)
    {
        if (file_exists($pictureWithCompletePath)) {
            unlink($pictureWithCompletePath);
        }
    }
}
