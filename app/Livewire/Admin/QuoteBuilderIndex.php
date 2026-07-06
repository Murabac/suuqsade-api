<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Quote Builder')]
class QuoteBuilderIndex extends Component
{
    public function render()
    {
        return view('livewire.admin.quote-builder-index');
    }
}
