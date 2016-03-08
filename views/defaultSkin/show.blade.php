{{ XeFrontend::title($title) }}

{!! uio('contentCompiler', ['content' => $content]) !!}

@if(Auth::check() && in_array(Auth::user()->rating, ['super', 'manager']))
    <a class="btn btn-default" href="{!! route('manage.plugin.page.edit', $pageId) !!}">페이지 관리자 바로가기</a>
@endif

@if ($config->get('comment') === true)
{!! uio('comment', ['target' => $page]) !!}
@endif
