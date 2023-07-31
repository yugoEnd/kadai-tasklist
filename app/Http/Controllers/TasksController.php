<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\Models\Task;

class TasksController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = [];
        if (\Auth::check()) { // 認証済みの場合
            // 認証済みユーザを取得
            $user = \Auth::user();
            // ユーザの投稿の一覧を作成日時の降順で取得
            // （後のChapterで他ユーザの投稿も取得するように変更しますが、現時点ではこのユーザの投稿のみ取得します）
            $tasks = $user->tasks()->orderBy('created_at', 'desc')->paginate(10);
            $data = [
                'user' => $user,
                'tasks' => $tasks,
            ];
        }
        
        // dashboardビューでそれらを表示
        return view('dashboard', $data);
    }

      /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
         // getでtasks/createにアクセスされた場合の「新規登録画面表示処理
    public function create()
    {
         $tasks = new Task;

        // タスク作成ビューを表示
        return view('tasks.create', [
            'tasks' => $tasks,
        ]); 
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
     // postでtasks/にアクセスされた場合の「新規登録処理」
    public function store(Request $request)
    {
       // バリデーション
       $request->validate([
            'status' => 'required|max:255', // statusもバリデーション対象に追加
            'content' => 'required|max:255',
        ]);
    

         // 認証済みユーザ（閲覧者）の投稿として作成（リクエストされた値をもとに作成）
        $request->user()->tasks()->create([
            'status' => $request->status,
            'content' => $request->content,
        ]);

        // 前のURLへリダイレクトさせる
        return redirect('/');
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
      // getでtasks/idにアクセスされた場合の「取得表示処理」
      public function show($id)
    {
        // idの値でタスクを検索して取得
        $task = Task::findOrFail($id);

        // 認証済みユーザがその投稿の所有者でない場合はトップページにリダイレクトする
        if (Auth::id() !== $task->user_id) {
            return redirect('/');
        }
        // ユーザーの投稿一覧を作成日時の降順で取得
        $tasks = $task->user->tasks()->orderBy('created_at', 'desc')->paginate(10);

        // タスク詳細ビューでそれを表示
        return view('tasks.show', [
            'task' => $task,
            
        ]);
    
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
         // getでtasks/id/editにアクセスされた場合の「更新画面表示処理」
   public function edit($id)
    {
       // idの値でタスクを検索して取得
       $task = \App\Models\Task::findOrFail($id);
    
        // 認証済みユーザがその投稿の所有者でない場合はトップページにリダイレクトする
        if (\Auth::id() !== $task->user_id) {
            return redirect('/');
        }

        // タスク編集ビューでそれを表示
        return view('tasks.edit', [
        'task' => $task,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
     // putまたはpatchでtasks/idにアクセスされた場合の「更新処理」
    public function update(Request $request, $id)
    {
          $request->validate([
            'content' => 'required',
            'status' => 'required|max:10',
        ]);


         // idの値でtaskを検索して取得
        $task = Task::find($id);
        //タスクを更新
           $task->status = $request->status;
        $task->content = $request->content;
        $task->save();

        // トップページへリダイレクトさせる
        return redirect('/');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
      // deleteでtasks/idにアクセスされた場合の「削除処理」
    public function destroy($id)
    {
        // idの値で投稿を検索して取得
        $task = \App\Models\Task::findOrFail($id);
       // 認証済みユーザ（閲覧者）がその投稿の所有者である場合は投稿を削除
        if (\Auth::id() === $task->user_id) {
            $task->delete();
            return redirect('/')
                ->with('success','Delete Successful');
        }
       // 前のURLへリダイレクトさせる
        return redirect('/')
             ->with('error', 'Delete Failed');
    }
}
