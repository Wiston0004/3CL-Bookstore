<?php

namespace App\Services;

use App\Models\Book;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CatalogFacade
{
    /**
     * Create or update a book, manage cover, categories, tags.
     * Pass 'id' to update.
     */
    public function createBook(array $data): Book
    {
        return DB::transaction(function () use ($data) {
            $book = isset($data['id']) ? Book::findOrFail($data['id']) : new Book();

            // Map price float to price_cents if provided
            if (isset($data['price'])) {
                $book->price = (float) $data['price'];
            }

            $book->fill([
                'title'       => $data['title'],
                'author'      => $data['author'] ?? null,
                'isbn'        => $data['isbn'] ?? null,
                'description' => $data['description'] ?? null,
            ]);

            if (isset($data['stock'])) {
                $book->stock = (int) $data['stock'];
            }

            // Handle cover upload (if coming as 'cover' file or 'cover_path' string)
            if (($data['cover'] ?? null) instanceof UploadedFile) {
                if ($book->cover_path && Storage::disk('public')->exists($book->cover_path)) {
                    Storage::disk('public')->delete($book->cover_path);
                }
                $path = $data['cover']->store('covers', 'public');
                $book->cover_path = $path;
            }

            $book->save();

            // Sync categories & tags if provided
            if (isset($data['category_ids'])) {
                $book->categories()->sync($data['category_ids']);
            }
            if (isset($data['tag_ids'])) {
                $book->tags()->sync($data['tag_ids']);
            }

            return $book->fresh(['categories','tags']);
        });
    }
}
