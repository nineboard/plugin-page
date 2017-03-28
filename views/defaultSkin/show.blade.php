{{ XeFrontend::title($title) }}

{!! compile($pageId, $content, true) !!}

@if(Auth::check() && in_array(Auth::user()->rating, ['super', 'manager']))
    <a class="btn btn-default" href="{!! route('manage.plugin.page.edit', $pageId) !!}">{{xe_trans('xe::goSettingPage')}}</a>
@endif

@if ($config->get('comment') === true)
    <div class="__xe_comment board_comment">
        {!! uio('comment', ['target' => $page]) !!}
    </div>
@endif
