@foreach ($store->themeConfig()['sections'] as $section)
    @includeIf(theme_view('sections.'.$section['type']), ['section' => $section])
@endforeach
