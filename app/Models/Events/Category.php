<?php
namespace App\Models\Events;

class Category {
    // DELETE event listener callback
    public function delete($category) {
        foreach ($category->todos as $todo) {
            $todo->delete();
        }
    }
}