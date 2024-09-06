<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Validator;

class QuestionsController extends Controller
{
    use HttpResponses;

    public function getQuestions(Request $request){
        $validator = Validator::make($request->all(), [
            'question_type' => 'required|string',
            'category' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->error([], 'Required data are invalid', $validator->errors(), 422);
        }

        $user = auth()->user();
        // $user->dob = $user->email_verified_at;
        // $user->zone = $user->remember_token;
        // $user->category = $user->password;
        
        $questions = Question::where('question_type', $request->question_type)
                    ->where('category', $request->category)->get();

        return $this->success(['questions' => $questions, 'user' => $user], 'Questions retrieved successfully');
    }

    public function submitAnswers(Request $request){
        $validator = Validator::make($request->all(), [
            'question_type' => 'required|string',
            'category' => 'required|string',
            'answers' => 'required|array',
        ]);

        if ($validator->fails()) {
            return $this->error([], 'Required data are invalid', $validator->errors(), 422);
        }
        $user = auth()->user();

        if ($request->question_type == 'multichoice') {
            $user->taken_quiz = 1;
        }elseif ($request->question_type == 'audio') {
            $user->taken_audio = 1;
        }

        $user->save();

        // write $request to a file
        $path = storage_path('app/answers/' . Str::slug($user->name) . '-' . now()->format('Y-m-d-H-i-s') . '.json');
        file_put_contents($path, json_encode($request->all(), JSON_PRETTY_PRINT));

        
        return $this->success(['user' => $user], 'Answers submitted successfully');
    }


    public function upload(Request $request){
        $validator = Validator::make($request->all(), [
            'question_type' => 'required|string|in:multichoice,audio',
            'question' => 'required',
            'category' => 'required|string',
            'options' => 'nullable|array',
            'correct_answer' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->error([], 'Required data are invalid', $validator->errors(), 422);
        }

        $user = auth()->user();

        if ($request->question_type == 'audio') {
            if ($request->hasFile('question') && $request->file('question')->isValid()) {
                $suffix = Str::slug($request->correct_answer) . '-' . $request->category;
                $newAudioName = $suffix .  '-' . uniqid()  .  '.' . $request->question->extension();
                $request->question->storeAs('public/audio', $newAudioName);

                // Construct the complete URL
                $audio_path = asset('storage/audio/' . $newAudioName);
            }
        }

        $question = Question::create([
            'question_type' => $request->question_type,
            'question' => $request->question_type == 'audio' ? $audio_path : $request->question,
            'category' => $request->category,
            'options' => $request->options ? json_encode($request->options) : json_encode([]),
            'correct_answer' => $request->correct_answer,
        ]);

       
        return $this->success(['question' => $question], 'Question submitted successfully');
    }
}
