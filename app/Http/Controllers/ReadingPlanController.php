<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Http\Requests\ReadingPlanRequest;

class ReadingPlanController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        //queryで取得条件変更可能なデータとしてデータを取得(->queryを付けると二重になってエラー)
        $query = ReadingPlan::with('book.reviews');
        //どの状態でも適応する条件
        $query -> where('user_id', $user->id);
        //inputで選択した値が"0"の時Laravel,PHPではif文が「嘘(false)」と判断するため検索失敗扱い->defaultが表示され全件が取得される。そのため「filled」を使用する
        if ($request->filled('status')) {
            $query -> where('status', $request->status);
        }

        $readingPlans = $query->get();

        return view('reading-plans.index', [
            'user' => $user,
            'currentStatus' => $request->status,
            'readingPlans' => $readingPlans,
        ]);
    }

    public function create()
    {
        $books = Book::all();

        return view('reading-plans.create', compact('books'));
    }

    public function store(ReadingPlanRequest $request)
    {
        $user = Auth::user();

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' =>$request->input('book_id'),
            'target_date' => $request->input('target_date'),
            'status' => '0',
        ]);

        return redirect()->route('reading-plans.index');
    }

    public function edit($planId)
    {
        $readingPlan = ReadingPlan::with('book')
                    -> findOrFail($planId);

        $this->authorize('edit', $readingPlan);

        return view('reading-plans.edit', compact('readingPlan'));
    }

    public function complete($planId)
    {
        $readingPlan = readingPlan::findOrFail($planId);

        $readingPlan->update([
            'completed_at' => now(),
            'status' => '2',
        ]);

        $this->authorize('complete', $readingPlan);

        return redirect()->route('reading-plans.index');
    }

    public function update(ReadingPlanRequest $request, $planId)
    {
        $user = Auth::user();

        $readingPlan = ReadingPlan::findOrFail($planId);

        $this->authorize('update', $readingPlan);

        $readingPlan->update([
            'target_date' => $request->input('target_date'),
        ]);

        return redirect()->route('reading-plans.index');
    }

    public function delete($planId)
    {
        $readingPlan = ReadingPlan::findOrFail($planId);

        $readingPlan->delete();

        $this->authorize('delete', $readingPlan);

        return redirect()->route('reading-plans.index');
    }
}
