<p align="center"> 
  <img src="https://raw.githubusercontent.com/xpressengine/plugin-page/master/icon.png">
 </p>

# XE3 Page Plugin

이 어플리케이션은 Xpressengine3(이하 XE3)의 플러그인 입니다.

이 플러그인은 XE3에서 페이지 추가 기능을 제공합니다.

[![License](http://img.shields.io/badge/license-GNU%20LGPL-brightgreen.svg)]

# Installation
### Console
```
$ php artisan plugin:install page
```

### Web install
- 관리자 > 플러그인 & 업데이트 > 플러그인 목록 내에 새 플러그인 설치 버튼 클릭
- `page` 검색 후 설치하기

### Ftp upload
- 다음의 페이지에서 다운로드
    * https://store.xpressengine.io/plugins/page
    * https://github.com/xpressengine/plugin-page/releases
- 프로젝트의 `plugins` 디렉토리 아래 `page` 디렉토리명으로 압축해제
- `page` 디렉토리 이동 후 `composer dump` 명령 실행

# Usage
페이지 추가는 아래 방법으로 가능합니다.
`관리자 > 사이트맵> 사이트 메뉴 편집`에서 `아이템 추가` 기능으로 페이지를 추가해서 사용합니다.
1. `아이템 추가` 클릭
2. Simple Page 선택 후 하단에 `다음` 클릭
3. itemURL, Item Title, Comment 사용 여부, Mobile 사용 여부 등 세부사항 입력
4. 등록

페이지 설정은 아래 방법으로 가능합니다.
`관리자 > 사이트맵 > 사이트 메뉴 편집`에서 설정을 지정합니다.
1. 설정할 페이지의 이름 클릭
2. `상세 설정` 클릭
3. 페이지의 제목, 내용을 작성
    - 페이지에 출력할 내용을 입력하거나 에디터의 `소스` 버튼을 클릭해서 html 코드를 사용할 수 있습니다.
4. 저장  

## License
이 플러그인은 LGPL라이선스 하에 있습니다. <https://opensource.org/licenses/LGPL-2.1>
