<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\Article;
use App\Models\Category;
use App\Models\Message;
use App\Models\Proposition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;

class HomeController extends Controller
{
    /**
     * Display the home page.
     */
    public function index()
    {
        $categories = Category::withCount('ads')->get();
        $featuredAds = Ad::featured()->with(['category', 'user'])->take(8)->get();
        $latestAds = Ad::with(['category', 'user', 'images'])
            ->latest()
            ->take(8)
            ->get();
        
        // Récupération des 3 derniers articles
        $articles = Article::with(['user'])
            ->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->take(3)  // Limité à 3 articles
            ->get()
            ->map(function ($article) {
                $article->formatted_date = $article->published_at->format('d M Y');
                return $article;
            });

        return view('home', compact('categories', 'featuredAds', 'latestAds', 'articles'));
    }

    /**
     * Display the dashboard.
     */
    public function dashboard()
    {
        $user = auth()->user();
        
        // Get proposition statistics
        $propositionStats = [
            'total' => Proposition::where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhereHas('ad', function($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
            })->count(),
            'pending' => Proposition::where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhereHas('ad', function($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
            })->where('status', 'pending')->count(),
            'accepted' => Proposition::where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhereHas('ad', function($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
            })->where('status', 'accepted')->count(),
            'completed' => Proposition::where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhereHas('ad', function($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
            })->where('status', 'completed')->count(),
            'rejected' => Proposition::where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhereHas('ad', function($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
            })->where('status', 'rejected')->count(),
            'cancelled' => Proposition::where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhereHas('ad', function($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
            })->where('status', 'cancelled')->count(),
        ];
        
        // Get recent messages
        $recentMessages = Message::where(function ($query) use ($user) {
            $query->where('sender_id', $user->id)
                  ->orWhere('receiver_id', $user->id);
        })
        ->with(['sender', 'receiver', 'proposition.ad'])
        ->orderBy('created_at', 'desc')
        ->take(3)
        ->get()
        ->map(function ($message) use ($user) {
            $otherUser = $message->sender_id === $user->id ? $message->receiver : $message->sender;
            return [
                'id' => $message->id,
                'content' => $message->content,
                'created_at' => $message->created_at,
                'is_read' => !is_null($message->read_at),
                'other_user' => [
                    'id' => $otherUser->id,
                    'name' => $otherUser->name,
                ],
                'type' => $message->proposition_id ? 'proposition' : 'direct',
                'ad' => $message->proposition ? [
                    'id' => $message->proposition->ad->id,
                    'title' => $message->proposition->ad->title,
                ] : null
            ];
        });

        // Get unread messages count
        $unreadCount = Message::where('receiver_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return view('dashboard', [
            'recentMessages' => $recentMessages,
            'unreadCount' => $unreadCount,
            'propositionStats' => $propositionStats,
        ]);
    }

    public function howItWorks()
    {
        return view('pages.how-it-works', [
            'metaTitle' => 'Comment ça marche - FAISTROQUER',
            'metaDescription' => 'Découvrez comment fonctionne FAISTROQUER, la plateforme d\'échange de biens et services. Apprenez à échanger en toute confiance.',
        ]);
    }

    public function faq()
    {
        return view('pages.faq', [
            'metaTitle' => 'FAQ - Questions fréquentes - FAISTROQUER',
            'metaDescription' => 'Trouvez les réponses à vos questions sur FAISTROQUER. Consultez notre FAQ pour tout savoir sur le fonctionnement de la plateforme.',
        ]);
    }

    public function help()
    {
        return view('pages.help', [
            'metaTitle' => 'Aide et Support - FAISTROQUER',
            'metaDescription' => 'Besoin d\'aide ? Consultez notre centre d\'aide pour trouver des réponses à vos questions et obtenir de l\'assistance.',
        ]);
    }
} 