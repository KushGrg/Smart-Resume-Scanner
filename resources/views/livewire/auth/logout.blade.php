<?php

use Livewire\Volt\Component;

new class extends Component {
    public function mount()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        
        return redirect('/');
    }
}; ?>

<div>
    
</div>
