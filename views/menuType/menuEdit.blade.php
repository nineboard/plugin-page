<div class="panel-group">
    <div class="panel">
        <div class="panel-heading">
            <div class="pull-left">
                <h3 class="panel-title">
                    Page 설정<small>Page의 기본적인 설정을 입력합니다</small>
                </h3>
            </div>
            <div class="pull-right">
                <a data-toggle="collapse" data-parent="#accordion" href="#collapse-page-options" class="btn-link panel-toggle pull-right"><i class="xi-angle-down"></i><i class="xi-angle-up"></i><span class="sr-only">메뉴닫기</span></a>
            </div>
        </div>

        <div id="collapse-page-options" class="panel-collapse collapse in">
            <div class="panel-body">
                <div class="form-group">
                    <label for="page-option-comment">Comment</label>

                    <select id="page-option-comment" name="comment" class="form-control">
                        <option value="true" {{($config->get('comment') == true) ? 'selected="selected"' : ''}} >Use</option>
                        <option value="false" {{($config->get('comment') == false) ? 'selected="selected"' : ''}}>Disuse</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="page-option-mobile">Mobile Content</label>

                    <select id="page-option-mobile" name="mobile" class="form-control">
                        <option value="true" {{($config->get('mobile') == true) ? 'selected="selected"' : ''}} >Use</option>
                        <option value="false" {{($config->get('mobile') == false) ? 'selected="selected"' : ''}}>Disuse</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<input type="hidden" name="pageUid" value="{{$config->get('pageUid')}}"/>
