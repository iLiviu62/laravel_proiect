@extends('layouts.blog')

@section('title', $post->title . ' - Laravel Blog Tutorial')
@section('description', $post->excerpt ?: Str::limit(strip_tags($post->content), 160))

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Post Header -->
    <article class="bg-white rounded-lg shadow-lg overflow-hidden">
        @if($post->featured_image)
            <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" 
                 class="w-full h-64 object-cover">
        @endif
        
        <div class="p-8">
            <!-- Post Meta -->
            <div class="flex items-center text-sm text-gray-500 mb-4">
                <span>By {{ $post->user->name }}</span>
                <span class="mx-2">•</span>
                <time datetime="{{ $post->published_at->toISOString() }}">
                    {{ $post->published_at->format('F j, Y \a\t g:i A') }}
                </time>
                @if(!$post->is_published)
                    <span class="mx-2">•</span>
                    <span class="text-red-600 font-medium">Draft</span>
                @endif
            </div>
            
            <!-- Post Title -->
            <h1 class="text-3xl font-bold text-gray-900 mb-6">
                {{ $post->title }}
            </h1>
            
            <!-- Post Content -->
            <div class="prose prose-lg max-w-none">
                {!! $post->content !!}
            </div>
        </div>
    </article>

    <!-- Comments Section -->
    <div class="mt-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">
            Comments ({{ $post->approvedComments->count() }})
        </h2>

        @auth
            <!-- Comment Form -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <form method="POST" action="{{ route('comments.store', $post) }}">
                    @csrf
                    <div class="mb-4">
                        <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
                            Add a comment
                        </label>
                        <textarea 
                            name="content" 
                            id="content" 
                            rows="4" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Share your thoughts..."
                            required
                        >{{ old('content') }}</textarea>
                        @error('content')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">
                        Post Comment
                    </button>
                </form>
            </div>
        @else
            <div class="bg-gray-50 rounded-lg p-6 mb-8 text-center">
                <p class="text-gray-600">
                    <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-800">Login</a> 
                    or 
                    <a href="{{ route('register') }}" class="text-blue-600 hover:text-blue-800">register</a> 
                    to leave a comment.
                </p>
            </div>
        @endauth

        <!-- Comments List -->
        <div class="space-y-6">
            @forelse($post->approvedComments as $comment)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <!-- Comment Header -->
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center text-sm text-gray-500">
                            <span class="font-medium text-gray-900">{{ $comment->user->name }}</span>
                            <span class="mx-2">•</span>
                            <time datetime="{{ $comment->created_at->toISOString() }}">
                                {{ $comment->created_at->format('M j, Y \a\t g:i A') }}
                            </time>
                        </div>
                        
                        @auth
                            @if($comment->user_id === auth()->id() || auth()->user()->is_admin)
                                <form method="POST" action="{{ route('comments.destroy', $comment) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="text-red-600 hover:text-red-800 text-sm"
                                            onclick="return confirm('Are you sure you want to delete this comment?')">
                                        Delete
                                    </button>
                                </form>
                            @endif
                        @endauth
                    </div>
                    
                    <!-- Comment Content -->
                    <div class="text-gray-700">
                        {{ $comment->content }}
                    </div>

                    <!-- Replies -->
                    @if($comment->approvedReplies->count() > 0)
                        <div class="mt-4 ml-8 space-y-4">
                            @foreach($comment->approvedReplies as $reply)
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center text-sm text-gray-500">
                                            <span class="font-medium text-gray-900">{{ $reply->user->name }}</span>
                                            <span class="mx-2">•</span>
                                            <time datetime="{{ $reply->created_at->toISOString() }}">
                                                {{ $reply->created_at->format('M j, Y \a\t g:i A') }}
                                            </time>
                                        </div>
                                        
                                        @auth
                                            @if($reply->user_id === auth()->id() || auth()->user()->is_admin)
                                                <form method="POST" action="{{ route('comments.destroy', $reply) }}" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="text-red-600 hover:text-red-800 text-sm"
                                                            onclick="return confirm('Are you sure you want to delete this reply?')">
                                                        Delete
                                                    </button>
                                                </form>
                                            @endif
                                        @endauth
                                    </div>
                                    <div class="text-gray-700">
                                        {{ $reply->content }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Reply Form -->
                    @auth
                        <div class="mt-4 ml-8">
                            <form method="POST" action="{{ route('comments.store', $post) }}" class="reply-form" style="display: none;">
                                @csrf
                                <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                <div class="mb-3">
                                    <textarea 
                                        name="content" 
                                        rows="3" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Write a reply..."
                                        required
                                    ></textarea>
                                </div>
                                <div class="flex space-x-2">
                                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm">
                                        Reply
                                    </button>
                                    <button type="button" class="cancel-reply bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 text-sm">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                            <button class="show-reply-form text-blue-600 hover:text-blue-800 text-sm mt-2">
                                Reply to this comment
                            </button>
                        </div>
                    @endauth
                </div>
            @empty
                <div class="text-center py-8">
                    <p class="text-gray-500">No comments yet. Be the first to comment!</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Back to Posts Link -->
    <div class="mt-12 text-center">
        <a href="{{ route('home') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800">
            ← Back to all posts
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle reply form toggle
    document.querySelectorAll('.show-reply-form').forEach(button => {
        button.addEventListener('click', function() {
            const replyForm = this.parentNode.querySelector('.reply-form');
            replyForm.style.display = 'block';
            this.style.display = 'none';
        });
    });

    document.querySelectorAll('.cancel-reply').forEach(button => {
        button.addEventListener('click', function() {
            const replyForm = this.closest('.reply-form');
            const showButton = replyForm.parentNode.querySelector('.show-reply-form');
            replyForm.style.display = 'none';
            showButton.style.display = 'block';
        });
    });
});
</script>
@endsection