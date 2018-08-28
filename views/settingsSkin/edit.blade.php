
<div class="row">
    <div class="col-sm-12">
        <div class="panel-group">
            <div class="panel">
                <div id="collapseOne" class="panel-collapse collapse in">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <div class="clearfix">
                                        <label>Select locale <small>Select locale for editing contents. </small></label>
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
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-12">
        <div class="panel-group">
            <div class="panel">
                <div class="panel-heading">
                    <div class="pull-left">
                        <h3 class="panel-title">Page Pc Contents Setting</h3>
                    </div>
                </div>

                <form method="post" name="pcContent" action="{{ route('manage.plugin.page.update', $pcPage->getInstanceId()) }}" enctype="multi-form/data">
                <input type="hidden" name="mode" value="pc" />
                <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                <input type="hidden" name="id" value="{{$pcPage->id}}" />
                <input type="hidden" name="locale" value="{{$currentLocale}}" />
                <div class="panel-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <div class="clearfix">
                                    <label>Page title</label>
                                </div>
                                <input type="text" name="pageTitle" class="form-control" value="{{ $pcPage->title }}"/>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <div class="clearfix">
                                    <label>{{xe_trans('xe::content')}}</label>
                                </div>
                                {!! editor($pageId, ['contentDomId' => 'xePcContentEditor', 'content' => $pcPage->content], $pcPage->id) !!}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel-footer">
                    <div class="pull-right">
                        <button type="submit" class="btn btn-primary"><i class="xi-download"></i>{{xe_trans('xe::save')}}</button>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-sm-12">
        <div class="panel-group">
            <div class="panel">
                <div class="panel-heading">
                    <div class="pull-left">
                        <h3 class="panel-title">Page Mobile Contents Setting</h3>
                    </div>
                </div>
                @if($config->get('mobile'))
                <form method="post" name="mobileContent" action="{{ route('manage.plugin.page.update', $mobilePage->getInstanceId()) }}" enctype="multi-form/data">
                    <input type="hidden" name="mode" value="mobile" />
                    <input type="hidden" name="m" value="1" />
                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                    <input type="hidden" name="id" value="{{$mobilePage->id}}" />
                    <input type="hidden" name="locale" value="{{$currentLocale}}" />
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <div class="clearfix">
                                        <label>Page title</label>
                                    </div>
                                    <input type="text" name="pageTitle" class="form-control" value="{{ $mobilePage->title }}"/>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <div class="clearfix">
                                        <label>{{xe_trans('xe::content')}}</label>
                                    </div>
                                    {!! editor($pageId, ['contentDomId' => 'xeMobileContentEditor', 'content' => $mobilePage->content], $mobilePage->id) !!}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel-footer">
                        <div class="pull-right">
                            <button type="submit" class="btn btn-primary"><i class="xi-download"></i>{{xe_trans('xe::save')}}</button>
                        </div>
                    </div>
                </form>
                @else
                    <div class="panel-body">
                        {{xe_trans('page::msgMobileDeactivated')}} <a href="{{route('settings.menu.edit.item', [$menuId, $pageId])}}">[{{xe_trans('xe::goSettingPage')}}]</a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-sm-12">
        <div class="panel-group">
            <div class="panel">
                <div class="panel-heading">
                    <div class="pull-left">
                        <h3 class="panel-title">Comment Setting</h3>
                    </div>
                </div>
                <div class="panel-body">
                    @if($config->get('comment'))
                        {{xe_trans('page::msgGoToCommentSettingPage')}} <a href="{{ app('xe.plugin.comment')->getInstanceSettingURI($pageId) }}">[{{xe_trans('xe::goSettingPage')}}]</a>
                    @else
                        {{xe_trans('page::msgCommentDeactivated')}} <a href="{{route('settings.menu.edit.item', [$menuId, $pageId])}}">[{{xe_trans('xe::goSettingPage')}}]</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.jQuery(function($) {
    $('.__xe_btn_preview').on('click', function() {
        var form = $(this).closest('form');

        var currentUrl = form.attr('action');
        var cuurentTarget = form.attr('target');

        form.attr('action', '{{instance_route('preview', [], $pageId) }}');
        form.attr('target', '_blank');

        form.submit();

        form.attr('action', currentUrl);
        form.attr('target', cuurentTarget === undefined ? '' : cuurentTarget);
    });
});
</script>
