@section('page_title')
    <h2>폐이지 모듈 상세 설정</h2>
@stop

<div class="panel panel-default" id="panel10">
    <div class="panel-heading">
        <h4 class="panel-title">
            <a data-toggle="collapse" data-target="#pcContent"
               href="#pcContent" >
                Set locale
            </a>
        </h4>
    </div>

    <div id="pcContent" class="panel-collapse collapse in">
        <div class="panel-body">
            <dl>
                <dt>Select locale for editing contents.</dt>
                <dd>
                    <ul>
                        @foreach($locales as $locale)
                            <li>
                                @if(Request::get('locale') == $locale) > @endif

                                <a href="{{ route('manage.plugin.page.edit', ['id' => $pageId, 'locale' => $locale]) }}" @if(Request::get('locale') == $locale) class="active" @endif >{{ $locale }}</a>

                                @if(empty($config->get('pcUids')[$locale])) [PC emtpy] @endif
                                @if(empty($config->get('mobileUids')[$locale])) [Mobile emtpy] @endif

                            </li>
                        @endforeach
                    </ul>
                </dd>
            </dl>
        </div>
    </div>
</div>

<div class="panel panel-default" id="panel1">
    <div class="panel-heading">
        <h4 class="panel-title">
            <a data-toggle="collapse" data-target="#pcContent"
               href="#pcContent" >
                Page Pc Contents Setting
            </a>
        </h4>

    </div>
    <form method="post" name="pcContent" action="{{ route('manage.plugin.page.update', $pcPage->pageId) }}" enctype="multi-form/data">
        <input type="hidden" name="_method" value="put" />
        <input type="hidden" name="mode" value="pc" />
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
        <input type="hidden" name="id" value="{{$pcPage->content->id}}" />
        <input type="hidden" name="locale" value="{{$currentLocale}}" />

        <div id="pcContent" class="panel-collapse collapse in">
            <div class="panel-body">
                <dl>
                    <dt>Page Title</dt>
                    <dd>
                        <input type="text" name="pageTitle" class="form-control" value="{{ $pcPage->content->title }}"/></dd>
                </dl>
                <dl>
                    <dt>내용</dt>
                    <dd class="__xe_content">
                        {!! uio('editor', [
                        'contentDomId' => 'xePcContentEditor',
                        'content' => $pcPage->content->content,
                        'editorConfig' => [
                        'fileUpload' => [
                        'upload_url' => route('manage.plugin.page.upload', ['pageId' => $pageId]),
                        'source_url' => instanceRoute('source', [], $pageId),
                        'download_url' => instanceRoute('download', [], $pageId),
                        ],
                        'suggestion' => [
                        'hashtag_api' => route('manage.plugin.page.hashTag', ['pageId' => $pageId]),
                        'mention_api' => route('manage.plugin.page.mention', ['pageId' => $pageId]),
                        ],
                        ]
                        ]) !!}
                    </dd>
                </dl>
                <dl>
                    <dt>
                        <button type="submit" class="btn btn-primary"> Save </button>
                        <a class="btn btn-default" href="{{ URL::previous()  }}">Cancel</a>

                        <button type="button" class="btn btn-primary __xe_btn_preview">미리보기</button>
                    </dt>
                </dl>
            </div>
        </div>
    </form>
</div>

<div class="panel panel-default" id="panel2">
    <div class="panel-heading">
        <h4 class="panel-title">
            <a data-toggle="collapse" data-target="#mobileContent"
               href="#mobileContent" >
                Page Mobile Contents Setting
            </a>
        </h4>

    </div>
    @if($config->get('mobile'))
    <div id="mobileContent" class="panel-collapse collapse in">
        <form method="post" name="mobileContent" action="{{ route('manage.plugin.page.update', $mobilePage->pageId) }}" enctype="multi-form/data">
            <input type="hidden" name="_method" value="put" />
            <input type="hidden" name="mode" value="mobile" />
            <input type="hidden" name="m" value="1" />
            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
            <input type="hidden" name="id" value="{{$mobilePage->content->id}}" />
            <input type="hidden" name="locale" value="{{$currentLocale}}" />
            <div class="panel-body">
                <dl>
                    <dt>Page Title</dt>
                    <dd>
                        <input type="text" name="pageTitle" class="form-control" value="{{ $mobilePage->content->title }}"/></dd>
                </dl>
                <dl>
                    <dt>내용</dt>
                    <dd class="__xe_content">
                        {!! uio('editor', [
                        'contentDomId' => 'xeMobileContentEditor',
                        'content' => $mobilePage->content->content,
                        'editorConfig' => [
                        'fileUpload' => [
                        'upload_url' => route('manage.plugin.page.upload', ['pageId' => $pageId]),
                        'source_url' => instanceRoute('source', [], $pageId),
                        'download_url' => instanceRoute('download', [], $pageId),
                        ],
                        'suggestion' => [
                        'hashtag_api' => route('manage.plugin.page.hashTag', ['pageId' => $pageId]),
                        'mention_api' => route('manage.plugin.page.mention', ['pageId' => $pageId]),
                        ],
                        ]
                        ]) !!}
                    </dd>
                </dl>
                <dl>
                    <dt>
                        <button type="submit" class="btn btn-primary"> Save </button>
                        <a class="btn btn-default" href="{{ URL::previous()  }}">Cancel</a>

                        <button type="button" class="btn btn-primary __xe_btn_preview">미리보기</button>
                    </dt>
                </dl>
            </div>
        </form>
    </div>
    @else
        <div id="mobileContent" class="panel-collapse collapse in">
            <div class="panel-body">
                모바일 컨텐츠 사용이 설정되어 있지 않습니다. <a href="{{route('settings.menu.edit.item', [$menuId, $pageId])}}">[설정 페이지로 이동]</a>
            </div>
        </div>
    @endif
</div>

<div class="panel panel-default" id="panel3">
    <div class="panel-heading">
        <h4 class="panel-title">
            <a data-toggle="collapse" data-target="#commentSection"
               href="#commentSection" >
                Comment Setting
            </a>
        </h4>

    </div>
    <div id="commentSection" class="panel-collapse collapse">
        <div class="panel-body">
            @if($config->get('comment'))
            {!! $commentSection !!}
            @else
            코멘트 사용이 설정되어 있지 않습니다. <a href="{{route('settings.menu.edit.item', [$menuId, $pageId])}}">[설정 페이지로 이동]</a>
            @endif
        </div>
    </div>
</div>

<script type="text/javascript">

    XE.$(function($) {
        $('.__xe_btn_preview').on('click', function() {
            var form = $(this).closest('form');

            var currentUrl = form.attr('action');
            var cuurentTarget = form.attr('target');

            form.attr('action', '{{instanceRoute('preview', [], $pageId) }}');
            form.attr('target', '_blank');

            form.submit();

            form.attr('action', currentUrl);
            form.attr('target', cuurentTarget === undefined ? '' : cuurentTarget);
        });
    });

</script>
