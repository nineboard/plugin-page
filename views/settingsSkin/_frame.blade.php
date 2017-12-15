{{-- $_active 는 SettingsSkin 에서 처리됨 --}}
<ul class="nav nav-tabs">
    <li @if($_active == 'edit') class="active" @endif><a href="{{ route('manage.plugin.page.edit', ['pageId' => $pageId]) }}">{{xe_trans('xe::config')}}</a></li>
    <li @if($_active == 'editor') class="active" @endif><a href="{{ route('manage.plugin.page.editor', ['pageId' => $pageId]) }}">{{xe_trans('xe::editor')}}</a></li>
</ul>

{{-- include contents blade file --}}
@section('content')
    {!! isset($content) ? $content : '' !!}
@show
