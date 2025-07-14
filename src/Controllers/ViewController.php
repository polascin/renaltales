<?php

declare(strict_types=1);

namespace RenalTales\Controllers;

use RenalTales\Core\SecurityManager;
use RenalTales\Core\SessionManager;
use RenalTales\Views\View;

class ViewController {

  private View $view;
  private SecurityManager $securityManager;
  private SessionManager $sessionManager;

  private function __construct() {
    $view = new View();
    $page = $this->getCurrentPage();
    $this->display($page);
  }

  public function getCurrentPage(): string {
    return $_GET['page'] ?? 'home';
  }

  public function display($page = 'home'): void {
    switch ($page) {
      case 'home':
        echo $this->view->render('home');
        break;
      case 'about':
        echo $this->view->render('about');
        break;
      case 'contact':
        echo $this->view->render('contact');
        break;
      default:
        echo $this->view->render('404');
        break;
    }
  }
}
