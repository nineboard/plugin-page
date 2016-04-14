<div class="panel-group">
    <div class="panel">
        <div class="panel-heading">
            <div class="pull-left">
                <h3 class="panel-title">Page 의 기본적인 설정을 입력합니다.</h3>
            </div>
        </div>
        <div class="panel-body">
            <div class="form-group">
                <label class="text-title">Comment</label>
                <select name="comment" class="form-control">
                    <option value="true" {{($config->get('comment') == true) ? 'selected="selected"' : ''}} >Use</option>
                    <option value="false" {{($config->get('comment') == false) ? 'selected="selected"' : ''}}>Disuse</option>
                </select>
            </div>
            <div class="form-group">
                <label class="text-title">Mobile Content</label>
                <select name="mobile" class="form-control">
                    <option value="true" {{($config->get('mobile') == true) ? 'selected="selected"' : ''}} >Use</option>
                    <option value="false" {{($config->get('mobile') == false) ? 'selected="selected"' : ''}}>Disuse</option>
                </select>
            </div>
        </div>
    </div>
</div>
