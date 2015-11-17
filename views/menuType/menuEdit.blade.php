<div class="panel menu_detail">
    <div class="panel-heading">
        <div class="row">
            <p class="txt_tit">Page 의 기본적인 설정을 입력합니다.</p>
            <div class="right_btn">
                <button class="ico_gray"><i class="xi-angle-down"></i><span class="blind">닫기</span></button>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <div class="row v2">
            <dl>
                <dt>Comment</dt>
                <dd>
                    <select name="comment" class="form-control">
                        <option value="true" {{($config->get('comment') == true) ? 'selected="selected"' : ''}} >Use</option>
                        <option value="false" {{($config->get('comment') == false) ? 'selected="selected"' : ''}}>Disuse</option>
                    </select>
                </dd>
            </dl>
            <dl>
                <dt>Mobile Content</dt>
                <dd>
                    <select name="mobile" class="form-control">
                        <option value="true" {{($config->get('mobile') == true) ? 'selected="selected"' : ''}} >Use</option>
                        <option value="false" {{($config->get('mobile') == false) ? 'selected="selected"' : ''}}>Disuse</option>
                    </select>
                </dd>
            </dl>
        </div>
    </div>
</div>
<input type="hidden" name="pageUid" value="{{$config->get('pageUid')}}"/>
