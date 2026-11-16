<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\BlogPost;
use App\Models\Destination;
use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogAndSearchTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function login(string $role = 'admin'): void
    {
        $this->actingAs(AdminUser::create(['name' => 'T', 'email' => $role.'-blogtest@test.local', 'password' => 'test-password-123', 'role' => $role]), 'admin');
    }

    public function test_home_shows_published_blog_posts_and_hides_drafts(): void
    {
        BlogPost::create(['title' => 'Secret Draft Post', 'slug' => 'secret-draft', 'is_published' => false]);
        BlogPost::create(['title' => 'Future Post', 'slug' => 'future', 'is_published' => true, 'published_at' => now()->addWeek()->toDateString()]);

        $this->get('/')->assertOk()->assertSee('Beyond Amsterdam')->assertDontSee('Secret Draft Post')->assertDontSee('Future Post');
        $this->get('/blog/secret-draft')->assertNotFound();
        $this->get('/blog/future')->assertNotFound();
        $this->get('/blog')->assertOk()->assertDontSee('Secret Draft Post');
    }

    public function test_blog_post_page_and_sitemap(): void
    {
        $this->get('/blog/beyond-amsterdam-five-hidden-gems')->assertOk()->assertSee('BlogPosting', false);
        $this->get('/sitemap.xml')->assertSee(route('blog.show', 'beyond-amsterdam-five-hidden-gems'), false)->assertSee(route('blog.index'), false);
    }

    public function test_admin_can_create_blog_post_and_it_appears_publicly(): void
    {
        $this->login();
        $this->get('/admin/blog')->assertOk();
        $this->post('/admin/blog', ['title' => 'Tulip Season Tips', 'excerpt' => 'When to go.', 'body' => '<p>Hello</p>', 'is_published' => '1'])->assertRedirect(route('admin.blog.index'));

        $post = BlogPost::where('slug', 'tulip-season-tips')->firstOrFail();
        $this->assertNotNull($post->published_at, 'publishing without a date defaults to today');
        $this->get('/blog/tulip-season-tips')->assertOk()->assertSee('Tulip Season Tips');
        $this->get('/')->assertSee('Tulip Season Tips');

        $this->delete('/admin/blog/'.$post->id)->assertRedirect();
        $this->assertDatabaseMissing('blog_posts', ['id' => $post->id]);
    }

    public function test_editor_can_manage_blog(): void
    {
        $this->login('editor');
        $this->get('/admin/blog')->assertOk();
        $this->get('/admin/blog/create')->assertOk();
    }

    public function test_package_search_by_location(): void
    {
        $dest = Destination::first();
        $other = Destination::where('id', '!=', $dest->id)->first();
        Package::create(['title' => 'Alpha Journey', 'slug' => 'alpha-journey', 'destination_id' => $dest->id, 'is_published' => true]);
        Package::create(['title' => 'Beta Journey', 'slug' => 'beta-journey', 'destination_id' => $other->id, 'is_published' => true]);

        // AJAX fragment used by the "Search by location" popup
        $this->get('/packages/search?q='.urlencode($dest->name))->assertOk()->assertSee('Alpha Journey')->assertDontSee('Beta Journey')->assertDontSee('<html', false);
        $this->get('/packages/search?q=zzzz-nowhere')->assertOk()->assertSee('No packages found');
        $this->get('/packages/search?q=')->assertOk()->assertSee('Type a place');
        $this->get('/packages/search?q=%25')->assertOk()->assertDontSee('Alpha Journey'); // wildcards are escaped, not injected
        $this->get('/packages/search?q='.urlencode('<script>x</script>'))->assertOk()->assertDontSee('<script>x', false);

        // The original filter page is unchanged and still has the popup trigger
        $this->get('/packages')->assertOk()->assertSee('Alpha Journey')->assertSee('Beta Journey')->assertSee('Search by location')->assertSee('name="type"', false);
    }

    public function test_home_gallery_can_be_toggled_from_the_admin(): void
    {
        $this->get('/')->assertSee('A glimpse of the'); // shown by default (original photos)

        $this->login();
        $home = \App\Models\Page::bySlug('home');
        $payload = ['title' => $home->title, 'c' => ['show_gallery' => '0']];

        $this->put('/admin/pages/'.$home->id, $payload)->assertSessionHasNoErrors();
        $this->get('/')->assertOk()->assertDontSee('A glimpse of the')->assertSee('Frequently Asked Questions');

        $payload['c']['show_gallery'] = '1';
        $this->put('/admin/pages/'.$home->id, $payload);
        $this->get('/')->assertSee('A glimpse of the');
    }

    public function test_home_gallery_content_is_editable_and_comes_after_testimonials(): void
    {
        $this->login();
        $home = \App\Models\Page::bySlug('home');
        $this->get('/admin/pages/'.$home->id.'/edit')->assertOk()->assertSee('Photo gallery section')->assertSee('gallery[items]', false);

        $this->put('/admin/pages/'.$home->id, [
            'title' => $home->title,
            'c' => ['show_gallery' => '1'],
            'gallery' => [
                'eyebrow' => 'Our photos', 'title' => 'Custom Gallery Heading',
                'items' => [
                    ['caption' => 'Windmill dawn', 'alt' => 'A windmill at dawn', 'image' => 'images/families.webp'],
                    ['caption' => 'No photo row', 'alt' => '', 'image' => ''],               // dropped: no photo
                    ['caption' => 'Evil', 'alt' => 'x', 'image' => 'https://evil.test/a.jpg'], // rejected by validation
                ],
            ],
        ])->assertSessionHasErrors('gallery.items.2.image');

        $this->put('/admin/pages/'.$home->id, [
            'title' => $home->title,
            'c' => ['show_gallery' => '1'],
            'gallery' => ['eyebrow' => 'Our photos', 'title' => 'Custom Gallery Heading', 'items' => [
                ['caption' => 'Windmill dawn', 'alt' => 'A windmill at dawn', 'image' => 'images/families.webp'],
                ['caption' => 'No photo row', 'alt' => '', 'image' => ''],
            ]],
        ])->assertSessionHasNoErrors();

        $html = $this->get('/')->assertOk()->assertSee('Custom Gallery Heading')->assertSee('Windmill dawn')->assertDontSee('No photo row')->assertDontSee('A glimpse of the')->getContent();
        $this->assertGreaterThan(strpos($html, 'What Travellers Say'), strpos($html, 'Custom Gallery Heading'), 'gallery is after the testimonials');
    }
}
