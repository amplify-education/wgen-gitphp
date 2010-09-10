{*
 *  heads.tpl
 *  gitphp: A PHP git repository browser
 *  Component: Head view template
 *
 *  Copyright (C) 2009 Christopher Han <xiphux@gmail.com>
 *}

 {include file='header.tpl'}

 {* Nav *}
 <div class="page_nav">
   <a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=summary">summary</a> | <a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=shortlog">shortlog</a> | <a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=log">log</a> | <a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=commit&h={$head}">commit</a> | <a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=commitdiff&h={$head}">commitdiff</a> | <a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=tree&hb={$head}">tree</a>
   <br /><br />
 </div>

 {include file='title.tpl' target='summary'}
 
  <table cellspacing="0">
    {include file='head_list.tpl' headlist=$headlist}
  </table>

 {include file='footer.tpl'}

