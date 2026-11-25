<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Capitalization, all pages: sentence case for headings, labels, buttons, tabs and menu items (capitals only for proper nouns and names).
 * Applies the same replacements as the code defaults to the saved page sections, menu items and settings.
 */
return new class extends Migration
{
    private function map(): array
    {
        return array (
  'Get a Free Quote' => 'Get a free quote',
  'Get Your Free Quote' => 'Get your free quote',
  'Get My Free Quote' => 'Get my free quote',
  'Get Instant Quote' => 'Get an instant quote',
  'Get Free Quote' => 'Get a free quote',
  'Get a Quote' => 'Get a quote',
  'Ask Us a Question' => 'Ask us a question',
  'Start Your Netherlands Dream Trip' => 'Start your Netherlands dream trip',
  'Start Your Adventure' => 'Start your adventure',
  'Free Quote' => 'Free quote',
  'All Packages' => 'All packages',
  'Our Articles' => 'Our articles',
  'What Our Travelers Say' => 'What our travelers say',
  'What Travelers Say' => 'What travelers say',
  'How It Works' => 'How it works',
  'Signature Collection' => 'Signature collection',
  'Travel With Confidence' => 'Travel with confidence',
  'Review Your Itinerary' => 'Review your itinerary',
  'Experience Authentic Dutch Culture' => 'Experience authentic Dutch culture',
  'Authentic Dutch Culture' => 'Authentic Dutch culture',
  'Expertly Crafted Itineraries' => 'Expertly crafted itineraries',
  'Custom Trips' => 'Custom trips',
  'Local Specialists' => 'Local specialists',
  'Frequently Asked Questions' => 'Frequently asked questions',
  'Send Message' => 'Send message',
  'Privacy Policy' => 'Privacy policy',
  'Terms and Conditions' => 'Terms and conditions',
  'Tour Types' => 'Tour types',
  'About Us' => 'About us',
  'Meet the Team' => 'Meet the team',
  'Welcome to the Best Netherlands Tours' => 'Welcome to the best Netherlands tours',
  '3 Easy Steps to Book Your Netherlands Tour' => '3 easy steps to book your Netherlands tour',
  'Ready to Start?' => 'Ready to start?',
  'Ready to Discover the *Real Netherlands*?' => 'Ready to discover the *real Netherlands*?',
  'Ready to Discover the Real Netherlands' => 'Ready to discover the real Netherlands',
);
    }

    private function fix(?string $text, bool $json): ?string
    {
        if ($text === null || $text === '') {
            return $text;
        }
        foreach ($this->map() as $from => $to) {
            $text = $json ? str_replace(trim(json_encode($from, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), '"'), trim(json_encode($to, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), '"'), $text) : ($text === $from ? $to : $text);
        }

        return $text;
    }

    public function up(): void
    {
        foreach (DB::table('pages')->get(['id', 'sections']) as $p) {
            $new = $this->fix($p->sections, true);
            if ($new !== $p->sections) {
                DB::table('pages')->where('id', $p->id)->update(['sections' => $new]);
            }
        }
        foreach (DB::table('menu_items')->get(['id', 'label', 'description']) as $m) {
            $u = ['label' => $this->fix($m->label, false), 'description' => $this->fix($m->description, false)];
            if ($u['label'] !== $m->label || $u['description'] !== $m->description) {
                DB::table('menu_items')->where('id', $m->id)->update($u);
            }
        }
        foreach (DB::table('settings')->get(['id', 'value']) as $s) {
            $new = $this->fix($s->value, false);
            if ($new !== $s->value) {
                DB::table('settings')->where('id', $s->id)->update(['value' => $new]);
            }
        }
    }
};
