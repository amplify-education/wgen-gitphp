{*
 *  blobdiff.tpl
 *  gitphp: A PHP git repository browser
 *  Component: Blobdiff view template
 *
 *  Copyright (C) 2009 Christopher Han <xiphux@gmail.com>
 *}

 {include file='header.tpl'}

 {* If we managed to look up commit info, we have enough info to display the full header - othewise just use a simple header *}
 <div class="page_nav">
   {include file='project_header.tpl' unselect='blobdiff' commit=$commit}
   <br />
   <a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=blobdiff_plain&h={$blob->GetHash()}&hp={$blobparent->GetHash()}&f={$file}">plain</a>
 </div>

 {include file='title.tpl' titlecommit=$commit}

 {include file='path.tpl' pathobject=$blobparent target='blob'}
 
 <div class="page_body">
   <div class="diff_info">
     {* Display the from -> to diff header *}
     blob:<a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=blob&h={$blobparent->GetHash()}&hb={$commit->GetHash()}&f={$file}">{if $file}a/{$file}{else}{$blobparent->GetHash()}{/if}</a> -&gt; blob:<a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=blob&h={$blob->GetHash()}&hb={$commit->GetHash()}&f={$file}">{if $file}b/{$file}{else}{$blob->GetHash()}{/if}</a>
   </div>
   {* Display the diff *}
   {include file='filediff.tpl' diff=$filediff->GetDiff($file, false, true)}
 </div>

 {include file='footer.tpl'}

