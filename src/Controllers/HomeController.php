<?php

declare(strict_types=1);

namespace RenalTales\Controllers;

use RenalTales\Http\Response;
use RenalTales\Helpers\Translation;
use RenalTales\Core\Template;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Home Controller
 *
 * Simplified controller implementing direct request flow:
 * Route → Controller method → Prepare data → Render template
 * Uses simple associative arrays for passing data to templates.
 *
 * @package RenalTales\Controllers
 * @version 2025.v3.1.dev
 * @author Ľubomír Polaščín
 */
class HomeController
{
    /**
     * @var Translation Translation helper
     */
    private Translation $translation;

    public function __construct()
    {
        $this->translation = $GLOBALS['translation'] ?? new Translation();
    }

    /**
     * Display home page
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        // Prepare data directly in controller
        $data = $this->prepareHomeData($request);

        // Render template directly
        $template = new Template();
        $html = $template->render('home', $data, true);

        return new Response(200, ['Content-Type' => 'text/html; charset=utf-8'], $html);
    }

    /**
     * Prepare home page data as simple associative array
     *
     * @param ServerRequestInterface $request
     * @return array
     */
    private function prepareHomeData(ServerRequestInterface $request): array
    {
        $currentLanguage = $this->translation->getCurrentLanguage();

        return [
            // Meta information
            'title' => $this->trans('home.title', 'RenalTales - Home'),
            'page_title' => $this->trans('home.title', 'RenalTales - Home'),
            'app_name' => 'RenalTales',
            'lang' => $currentLanguage,
            'language' => $currentLanguage,
            'year' => date('Y'),
            'asset_url' => '/assets',

            // Hero/Welcome section - matching template variables
            'welcome_title' => $this->trans('home.welcome', 'Welcome to RenalTales!'),
            'welcome_message' => $this->trans('home.description', 'Welcome to our supportive community for people affected by kidney disorders.'),
            'get_started_text' => $this->trans('get_started', 'Get Started'),

            // Hero section (additional)
            'hero_title' => $this->trans('home.welcome', 'Welcome to RenalTales!'),
            'hero_intro' => $this->trans('home.description', 'Welcome to our supportive community for people affected by kidney disorders.'),
            'hero_description' => $this->trans('home_intro2', 'This web application is designed to facilitate the sharing of personal experiences among individuals affected by kidney disorders.'),

            // Feature cards - matching template
            'feature1_title' => $this->trans('share_story', 'Share Your Story'),
            'feature1_description' => $this->trans('share_story_desc', 'Your experience matters. Share your journey to inspire and support others.'),
            'feature2_title' => $this->trans('read_stories', 'Read Stories'),
            'feature2_description' => $this->trans('read_stories_desc', 'Find inspiration and comfort in the experiences of others in our community.'),
            'feature3_title' => $this->trans('join_community', 'Join Community'),
            'feature3_description' => $this->trans('join_community_desc', 'Connect with others, participate in discussions, and build lasting friendships.'),
            'features' => $this->getFeatureCards(),

            // Navigation - matching template
            'home_text' => $this->trans('nav.home', 'Home'),
            'about_text' => $this->trans('nav.about', 'About'),
            'nav_home' => $this->trans('nav.home', 'Home'),
            'nav_stories' => $this->trans('nav.stories', 'Stories'),
            'nav_community' => $this->trans('nav.community', 'Community'),
            'nav_about' => $this->trans('nav.about', 'About'),
            'nav_login' => $this->trans('nav.login', 'Login'),
            'nav_register' => $this->trans('nav.register', 'Register'),

            // Footer - matching template
            'all_rights_reserved' => $this->trans('all_rights_reserved', 'All rights reserved.'),
            'privacy_text' => $this->trans('privacy', 'Privacy'),
            'terms_text' => $this->trans('terms', 'Terms'),
            'contact_text' => $this->trans('contact', 'Contact'),
            'footer_copyright' => $this->trans('footer_copyright', 'Ľubomír Polaščín'),

            // Language switcher
            'language_switcher' => '<select><!-- Language options --></select>',
            'language_label' => $this->trans('current_language', 'Language'),
            'current_language' => $currentLanguage,
            'supported_languages' => $this->getSupportedLanguages(),

            // Sidebar
            'sidebar_menu_title' => $this->trans('main_menu', 'Quick Navigation'),
            'sidebar_about_title' => $this->trans('about_renal_tales', 'About Renal Tales'),
            'sidebar_about_description' => $this->trans('renal_tales_description', 'Renal Tales is a supportive community platform where people affected by kidney disorders can share their experiences, find support, and connect with others on similar journeys.'),
            'sidebar_guidelines_title' => $this->trans('community_guidelines', 'Community Guidelines'),
            'sidebar_guidelines' => $this->getGuidelines(),
        ];
    }

    /**
     * Get feature cards data
     *
     * @return array
     */
    private function getFeatureCards(): array
    {
        return [
            [
                'title' => $this->trans('share_story', 'Share Your Story'),
                'description' => $this->trans('share_story_desc', 'Your experience matters. Share your journey to inspire and support others.'),
                'link' => '/stories/create',
                'button_text' => $this->trans('start_sharing', 'Start Sharing'),
                'button_class' => 'btn-primary'
            ],
            [
                'title' => $this->trans('read_stories', 'Read Stories'),
                'description' => $this->trans('read_stories_desc', 'Find inspiration and comfort in the experiences of others in our community.'),
                'link' => '/stories',
                'button_text' => $this->trans('browse_stories', 'Browse Stories'),
                'button_class' => 'btn-secondary'
            ],
            [
                'title' => $this->trans('join_community', 'Join Community'),
                'description' => $this->trans('join_community_desc', 'Connect with others, participate in discussions, and build lasting friendships.'),
                'link' => '/community',
                'button_text' => $this->trans('explore_community', 'Explore Community'),
                'button_class' => 'btn-primary'
            ]
        ];
    }

    /**
     * Get community guidelines
     *
     * @return array
     */
    private function getGuidelines(): array
    {
        return [
            $this->trans('guideline_respectful', 'Be respectful and supportive to all community members'),
            $this->trans('guideline_privacy', 'Respect privacy and confidentiality'),
            $this->trans('guideline_medical', 'Share experiences, not medical advice'),
            $this->trans('guideline_appropriate', 'Keep content appropriate and relevant'),
        ];
    }

    /**
     * Get supported languages with current language indication
     *
     * @return array
     */
    private function getSupportedLanguages(): array
    {
        $supportedLanguages = [
            'en' => 'English',
            'sk' => 'Slovak',
            'la' => 'Latin',
        ];

        $languages = [];
        $currentLanguage = $this->translation->getCurrentLanguage();

        foreach ($supportedLanguages as $code => $name) {
            $languages[] = [
                'code' => $code,
                'name' => $name,
                'selected' => $code === $currentLanguage
            ];
        }

        return $languages;
    }

    /**
     * Get translated text with fallback
     *
     * @param string $key Translation key
     * @param string $fallback Fallback text
     * @return string
     */
    private function trans(string $key, string $fallback = ''): string
    {
        return $this->translation->translate($key, $fallback);
    }
}
