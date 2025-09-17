<?php
// app/View/Components/Layout/AppBar.php
namespace App\View\Components\Layout;

use Illuminate\View\Component;

class AppBar extends Component
{
    public $title;
    
    public function __construct($title = null)
    {
        $this->title = $title;
    }
    
    public function render()
    {
        return view('components.layout.app-bar');
    }
}