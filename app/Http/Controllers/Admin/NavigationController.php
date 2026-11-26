<?php

namespace App\Http\Controllers\Admin;

use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Admin > Navigation: the header menu (links, the auto Tours menu, dropdowns of image cards) and the footer links. */
class NavigationController extends ResourceController
{
    protected function model(): string
    {
        return MenuItem::class;
    }

    protected function config(): array
    {
        $parents = ['' => '(none: top-level item)'] + MenuItem::where('menu', 'header')->whereNull('parent_id')->orderBy('sort_order')->pluck('label', 'id')->all();

        return [
            'title' => 'Menu item', 'plural' => 'Navigation', 'route' => 'admin.navigation', 'sortable' => true,
            'help' => 'The header menu and the footer links. A "Tours menu" item shows the automatic menu of tour types. To make a dropdown, add a "Dropdown of cards" item, then add cards to it by choosing it as the parent.',
            'columns' => ['Label' => 'label', 'Where' => 'location_label', 'Link' => 'link_label'],
            'fields' => [
                ['name' => 'menu', 'label' => 'Menu', 'type' => 'select', 'options' => ['header' => 'Header', 'footer' => 'Footer links'], 'default' => 'header', 'rules' => ['required', Rule::in(['header', 'footer'])]],
                ['name' => 'parent_id', 'label' => 'Put inside this dropdown (header only; cards of a dropdown)', 'type' => 'select', 'options' => $parents, 'rules' => 'nullable|integer|exists:menu_items,id'],
                ['name' => 'kind', 'label' => 'Type', 'type' => 'select', 'options' => ['link' => 'Normal link', 'tours' => 'Tours menu (automatic, from Tour types)', 'styles' => 'Travel styles menu (automatic, from Travel styles)', 'cards' => 'Dropdown of cards (add the cards below it)'], 'default' => 'link', 'rules' => ['required', Rule::in(['link', 'tours', 'styles', 'cards'])]],
                ['name' => 'label', 'label' => 'Text shown', 'type' => 'text', 'rules' => 'required|string|max:80'],
                ['name' => 'url', 'label' => 'Link: a page path like /about, a full https:// address, or #section', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:255', 'regex:#^(/[A-Za-z0-9_./?=&%\#-]*|https?://[^\s"<>]+|\#[A-Za-z0-9_-]+|tel:[+0-9 ()-]+|mailto:[^\s"<>]+)$#']],
                ['name' => 'description', 'label' => 'Short description (shown on dropdown cards)', 'type' => 'text', 'rules' => 'nullable|string|max:160'],
                ['name' => 'image', 'label' => 'Card image (dropdown cards only)', 'type' => 'image'],
                ['name' => 'new_tab', 'label' => 'Open in a new tab', 'type' => 'checkbox'],
                ['name' => 'is_active', 'label' => 'Active (visible on the site)', 'type' => 'checkbox'],
            ],
        ];
    }

    public function index()
    {
        $items = MenuItem::orderBy('menu')->orderByRaw('COALESCE(parent_id, id), parent_id IS NOT NULL')->orderBy('sort_order')->get();

        return view('admin.resource.index', ['cfg' => $this->config(), 'items' => $items]);
    }

    protected function prepare(array $data, Model $item, Request $request): array
    {
        $data['parent_id'] = ($data['menu'] ?? 'header') === 'header' && filled($data['parent_id'] ?? null) && (int) $data['parent_id'] !== (int) $item->id ? (int) $data['parent_id'] : null;
        $data['url'] = filled($data['url'] ?? null) ? $data['url'] : null;
        $data['description'] = filled($data['description'] ?? null) ? $data['description'] : null;

        return $data;
    }

    /** Move within the same menu and level (not across the whole table). */
    public function move($id, string $dir)
    {
        $item = MenuItem::findOrFail($id);
        $siblings = MenuItem::where('menu', $item->menu)->where('parent_id', $item->parent_id)->orderBy('sort_order')->orderBy('id')->get()->values();
        $i = $siblings->search(fn ($x) => $x->id === $item->id);
        $j = $dir === 'up' ? $i - 1 : $i + 1;
        if ($i !== false && isset($siblings[$j])) {
            $a = $siblings[$i]->sort_order;
            $b = $siblings[$j]->sort_order;
            if ($a === $b) {
                $b = $a + ($dir === 'up' ? -1 : 1);
            }
            $siblings[$i]->update(['sort_order' => $b]);
            $siblings[$j]->update(['sort_order' => $a]);
        }

        return back();
    }

    protected function cannotDelete(Model $item): ?string
    {
        return MenuItem::where('parent_id', $item->id)->exists() ? 'Delete or move the cards inside this dropdown first.' : null;
    }
}
