<?php

namespace App\Http\Controllers\back;

use App\Http\Controllers\Controller;
use App\Http\Requests\StepRequest;
use App\Models\Cmf;
use App\Models\Document;
use App\Models\Step;
use App\Models\Subdepartment;
use App\Models\User;
use Illuminate\Http\Request;

class DocumentControlController extends Controller
{
    public function index()
    {
        $cmfs = Cmf::get();
        return view('themes.back.dc.index',compact(
            'cmfs',
        ));
    }

    public function verifikasi($slug)
    {
        $cmf = Cmf::withCount('departments')->where([
            ['slug', $slug],
        ])->firstOrFail();
        $steps = Step::where('cmf_id', $cmf->id)->get();
        $depthead_pic = User::where('department_id', $cmf->user->department_id)->role('depthead pic')->first();

        $user_depthead = [];
        $cmf->departments->each(function ($item, $key) use (&$user_depthead) {
            $user_depthead[] = $item->pivot->department_id;
        });
        $depthead = User::role('depthead')->whereIn('department_id',$user_depthead)->get();
        $svp = User::role('svp system')->first();
        $mnf = User::role('mnf')->first();
        $mr = User::role('mr & food safety team')->first();
        $dc = User::role('document control')->first();
        $documents = Document::get();

        return view('themes.back.dc.verifikasi',compact(
            'cmf',
            'steps',
            'depthead_pic',
            'depthead',
            'svp',
            'mnf',
            'mr',
            'dc',
            'documents'
        ));
    }

    public function store($slug, Request $request)
    {
        $cmf = Cmf::where([
            ['slug', $slug],
        ])->firstOrFail();
        $user = User::find(auth()->user()->id)->first();
        $is_signature = $request['verifikasi'] == "setuju";
        if($cmf->step == 1 && auth()->user()->hasRole('document control')){
            $depthead_pic = User::where('name', $request['depthead_pic'])->first();
            $request->validate([
                'catatan' => 'required',
                'verifikasi' => 'required'
            ]);
            Step::create([
                'cmf_id' => $cmf->id,
                'user_id' => $depthead_pic->id,
                'step' => 2,
                'is_signature' => $is_signature,
                'catatan' => $request['catatan']
            ]);

            $cmf->update([
                'status_pengajuan' => $is_signature ? 'Pengajuan Request Perubahan Disetujui Depthead PIC' : 'Pengajuan Request Perubahan Tidak Disetujui Depthead PIC',
                'updated_by' => $user->name,
                'step' => 2
            ]);
            return back()
                ->with('message','Request Perubahan CMF Berhasil diverifikasi');
        }elseif($cmf->step == 2 && auth()->user()->hasRole('document control')){
            $depthead = User::whereIn('name', $request->depthead)->get();
            foreach ($depthead as $key => $item){
                Step::create([
                    'cmf_id' => $cmf->id,
                    'user_id' => $item->id,
                    'step' => 3,
                    'is_signature' => $request->verifikasi[$key] == "setuju",
                    'catatan' => $request->catatan[$key]
                ]);

                $cmf->update([
                    'status_pengajuan' => $request->verifikasi[$key] == "setuju" ? 'Pengajuan Request Perubahan Disetujui Depthead' : 'Pengajuan Request Perubahan Tidak Disetujui Depthead',
                    'updated_by' => $user->name,
                    'step' => 3
                ]);
            }
            return back()
                ->with('message','Request Perubahan CMF Berhasil diverifikasi');
        }elseif($cmf->step == 3 && auth()->user()->hasRole('document control')){
            $svp = User::where('name', $request['svp'])->first();
            Step::create([
                'cmf_id' => $cmf->id,
                'user_id' => $svp->id,
                'step' => 4,
                'is_signature' => $is_signature,
            ]);

            $cmf->update([
                'status_pengajuan' => $is_signature ? 'Pengajuan Request Perubahan Disetujui SVP' : 'Pengajuan Request Perubahan Tidak Disetujui SVP',
                'updated_by' => $user->name,
                'step' => 4
            ]);
            return back()
                ->with('message','Request Perubahan CMF Berhasil diverifikasi');
        }elseif($cmf->step == 4 && auth()->user()->hasRole('document control')){
            $mnf = User::where('name', $request['mnf'])->first();
            Step::create([
                'cmf_id' => $cmf->id,
                'user_id' => $mnf->id,
                'step' => 5,
                'is_signature' => $is_signature,
                'catatan' => $request['catatan']
            ]);

            $cmf->update([
                'status_pengajuan' => $is_signature ? 'Pengajuan Request Perubahan Disetujui MNF' : 'Pengajuan Request Perubahan Tidak Disetujui MNF',
                'updated_by' => $user->name,
                'step' => 5
            ]);
            return back()
                ->with('message','Request Perubahan CMF Berhasil diverifikasi');
        }elseif($cmf->step == 5 && auth()->user()->hasRole('document control')){
            $mr = User::where('name', $request['mr'])->first();
            Step::create([
                'cmf_id' => $cmf->id,
                'user_id' => $mr->id,
                'step' => 6,
                'is_signature' => $is_signature,
                'catatan' => $request['catatan']
            ]);

            $cmf->update([
                'status_pengajuan' => $is_signature ? 'Pengajuan Request Perubahan Disetujui MR' : 'Pengajuan Request Perubahan Tidak Disetujui MR',
                'updated_by' => $user->name,
                'step' => 6
            ]);
            return back()
                ->with('message','Request Perubahan CMF Berhasil diverifikasi');
        }elseif($cmf->step == 6 && auth()->user()->hasRole('document control')){
            $request->validate([
                'catatan' => 'required',
            ]);
            Step::create([
                'cmf_id' => $cmf->id,
                'user_id' => $cmf->user_id,
                'step' => 7,
                'is_signature' => true,
                'catatan' => $request['catatan']
            ]);

            $cmf->update([
                'status_pengajuan' => 'Pengajuan Review Setelah Dilakukan Perubahan',
                'updated_by' => $user->name,
                'step' => 7
            ]);
            return back()
                ->with('message','Pengajuan Review Setelah Dilakukan Perubahan Berhasil');
        }elseif($cmf->step == 7 && auth()->user()->hasRole('document control')){
            $request->validate([
                'verifikasi' => 'required',
                'catatan' => 'required',
            ]);
            $depthead_pic = User::where('name', $request['depthead_pic'])->first();
            Step::create([
                'cmf_id' => $cmf->id,
                'user_id' => $depthead_pic->id,
                'step' => 8,
                'is_signature' => $is_signature,
                'catatan' => $request['catatan']
            ]);

            $cmf->update([
                'status_pengajuan' => $is_signature ? 'Pengajuan Request Perubahan Disetujui Depthead PIC' : 'Pengajuan Request Perubahan Tidak Disetujui Depthead PIC',
                'updated_by' => $user->name,
                'step' => 8
            ]);
            return back()
                ->with('message','Request Perubahan CMF Berhasil disetujui');
        }elseif($cmf->step == 8 && auth()->user()->hasRole('document control')){
            $depthead = User::whereIn('name', $request->depthead)->get();
            foreach ($depthead as $key => $item){
                Step::create([
                    'cmf_id' => $cmf->id,
                    'user_id' => $item->id,
                    'step' => 9,
                    'is_signature' => $request->verifikasi[$key] == "setuju",
                    'catatan' => $request->catatan[$key]
                ]);

                $cmf->update([
                    'status_pengajuan' => $request->verifikasi[$key] == "setuju" ? 'Pengajuan Request Perubahan Disetujui Depthead' : 'Pengajuan Request Perubahan Tidak Disetujui Depthead',
                    'updated_by' => $user->name,
                    'step' => 9
                ]);
            }
            return back()
                ->with('message','Request Perubahan CMF Berhasil diverifikasi');
        }else if($cmf->step == 9 && auth()->user()->hasRole('document control')){
            $mr = User::where('name', $request['mr'])->first();
            Step::create([
                'cmf_id' => $cmf->id,
                'user_id' => $mr->id,
                'step' => 10,
                'is_signature' => $is_signature,
            ]);

            $cmf->update([
                'status_pengajuan' => $is_signature ? 'Pengajuan Request Perubahan Disetujui MR' : 'Pengajuan Request Perubahan Tidak Disetujui MR',
                'updated_by' => $user->name,
                'step' => 10
            ]);
            return back()
                ->with('message','Request Perubahan CMF Berhasil diverifikasi');
        }
        elseif($cmf->step == 10 && auth()->user()->hasRole('document control')){
            $dc = User::where('name', $request['dc'])->first();
            Step::create([
                'cmf_id' => $cmf->id,
                'user_id' => $dc->id,
                'step' => 11,
                'is_signature' => $is_signature,
            ]);

            $cmf->documents()->sync([
                1 => ['applicable' => $request->applicable[0] == 'applicable' ? 1 : 0],
                2 => ['applicable' => $request->applicable[1] == 'applicable' ? 1 : 0],
                3 => ['applicable' => $request->applicable[2] == 'applicable' ? 1 : 0],
                4 => ['applicable' => $request->applicable[3] == 'applicable' ? 1 : 0],
                5 => ['applicable' => $request->applicable[4] == 'applicable' ? 1 : 0],
                6 => ['applicable' => $request->applicable[5] == 'applicable' ? 1 : 0],
                7 => ['applicable' => $request->applicable[6] == 'applicable' ? 1 : 0],
            ]);

            $cmf->update([
                'status_pengajuan' => $is_signature ? 'Pengajuan Request Perubahan Disetujui DC' : 'Pengajuan Request Perubahan Tidak Disetujui DC',
                'updated_by' => $user->name,
                'step' => 11
            ]);
            return redirect()->route('cmf.dc.index')
                ->with('message','Request Perubahan CMF Berhasil diverifikasi');
        }
        else{
            return back()->with('message-error','Anda sudah melakukan verifikasi');
        }
    }

    public function print($slug)
    {
        $cmf = Cmf::where([
            ['slug', $slug],
        ])->firstOrFail();
        $subdepartments = Subdepartment::get();

        $step10 = Step::where([
            ['step',10],
            ['cmf_id', $cmf->id]
        ])->first();

        $step11 = Step::where([
            ['step',11],
            ['cmf_id', $cmf->id]
        ])->first();

        return view('themes.back.dc.print', compact(
            'cmf',
            'subdepartments',
            'step10',
            'step11',
        ));
    }
}
