<?php

declare(strict_types=1);

namespace RenalTales\Services;

use RenalTales\Helpers\Translation;

/**
 * Home Data Service
 *
 * Handles data preparation for the home page.
 * Extracts business logic from view components.
 *
 * @package RenalTales\Services
 * @version 2025.v3.1.dev
 * @author Ľubomír Polaščín
 */
class HomeDataService
{
    private Translation $translation;
    private array $supportedLanguages;

    public function __construct(?Translation $translation = null)
    {
        $this->translation = $translation ?? new Translation();
        $this->supportedLanguages = [
            'en' => 'English',
            'sk' => 'Slovak',
            'la' => 'Latin',
        ];
    }

    /**
     * Get home page data
     *
     * @return array Home page data
     */
    public function getHomePageData(): array
    {
        $currentLanguage = $this->translation->getCurrentLanguage();

        return [
            'meta' => $this->getMetaData($currentLanguage),
            'hero' => $this->getHeroData(),
            'features' => $this->getFeatureCardsData(),
            'navigation' => $this->getNavigationData(),
            'sidebar' => $this->getSidebarData(),
            'footer' => $this->getFooterData(),
            'languages' => $this->getLanguageData($currentLanguage),
        ];
    }

    /**
     * Get meta data
     *
     * @param string $currentLanguage Current language
     * @return array Meta data
     */
    private function getMetaData(string $currentLanguage): array
    {
        return [
            'title' => $this->trans('home.title', 'Renal Tales - Home'),
            'language' => $currentLanguage,
            'app_name' => 'RenalTales',
            'year' => date('Y'),
        ];
    }

    /**
     * Get hero section data
     *
     * @return array Hero section data
     */
    private function getHeroData(): array
    {
        return [
            'title' => $this->trans('home.welcome', 'Welcome to RenalTales!'),
            'intro' => $this->trans('home.description', 'Welcome to our supportive community for people affected by kidney disorders.'),
            'description' => $this->trans('home_intro2', 'This web application is designed to facilitate the sharing of personal experiences among individuals affected by kidney disorders.'),
        ];
    }

    /**
     * Get feature cards data
     *
     * @return array Feature cards data
     */
    private function getFeatureCardsData(): array
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
     * Get navigation data
     *
     * @return array Navigation data
     */
    private function getNavigationData(): array
    {
        return [
            'home' => $this->trans('nav.home', 'Home'),
            'stories' => $this->trans('nav.stories', 'Stories'),
            'community' => $this->trans('nav.community', 'Community'),
            'about' => $this->trans('nav.about', 'About'),
            'login' => $this->trans('nav.login', 'Login'),
            'register' => $this->trans('nav.register', 'Register'),
        ];
    }

    /**
     * Get sidebar data
     *
     * @return array Sidebar data
     */
    private function getSidebarData(): array
    {
        return [
            'main_menu_title' => $this->trans('main_menu', 'Quick Navigation'),
            'about_title' => $this->trans('about_renal_tales', 'About Renal Tales'),
            'about_description' => $this->trans('renal_tales_description', 'Renal Tales is a supportive community platform where people affected by kidney disorders can share their experiences, find support, and connect with others on similar journeys.'),
            'guidelines_title' => $this->trans('community_guidelines', 'Community Guidelines'),
            'guidelines' => [
                $this->trans('guideline_respectful', 'Be respectful and supportive to all community members'),
                $this->trans('guideline_privacy', 'Respect privacy and confidentiality'),
                $this->trans('guideline_medical', 'Share experiences, not medical advice'),
                $this->trans('guideline_appropriate', 'Keep content appropriate and relevant'),
            ],
        ];
    }

    /**
     * Get footer data
     *
     * @return array Footer data
     */
    private function getFooterData(): array
    {
        return [
            'copyright' => $this->trans('footer_copyright', 'Ľubomír Polaščín'),
            'year' => date('Y'),
        ];
    }

    /**
     * Get language data
     *
     * @param string $currentLanguage Current language
     * @return array Language data
     */
    private function getLanguageData(string $currentLanguage): array
    {
        $languages = [];
        foreach ($this->supportedLanguages as $code => $name) {
            $languages[] = [
                'code' => $code,
                'name' => $name,
                'selected' => $code === $currentLanguage
            ];
        }

        return [
            'label' => $this->trans('current_language', 'Language'),
            'current' => $currentLanguage,
            'supported' => $languages,
        ];
    }

    /**
     * Get translated text with fallback
     *
     * @param string $key Translation key
     * @param string $fallback Fallback text
     * @return string Translated text
     */
    private function trans(string $key, string $fallback = ''): string
    {
        return $this->translation->translate($key, $fallback);
    }
}
