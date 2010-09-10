{*
 *  shortlog.tpl
 *  gitphp: A PHP git repository browser
 *  Component: Shortlog view template
 *
 *  Copyright (C) 2009 Christopher Han <xiphux@gmail.com>
 *}

 {include file='header.tpl'}

 {* Nav *}
 <div class="page_nav">
   {include file='project_header.tpl' unselect='shortlog' commithash=$commit->GetHash()}
   <br />
   {if ($commit->GetHash() != $head->GetHash()) || ($page > 0)}
     <a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=shortlog">HEAD</a>
   {else}
     HEAD
   {/if}
     &sdot; 
   {if $page > 0}
     <a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=shortlog&h={$commit->GetHash()}&pg={$page-1}" accesskey="p" title="Alt-p">prev</a>
   {else}
     prev
   {/if}
     &sdot; 
   {if $hasmore}
     <a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=shortlog&h={$commit->GetHash()}&pg={$page+1}" accesskey="n" title="Alt-n">next</a>
   {else}
     next
   {/if}
   <br />
 </div>

 {include file='title.tpl' target='summary'}
 
 <table cellspacing="0">
   {include file='shortlog_list.tpl' revlist=$revlist}

   {if $hasmore}
     <tr>
       <td><a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=shortlog&h={$commit->GetHash()}&pg={$page+1}" title="Alt-n">next</a></td>
     </tr>
   {/if}
 </table>

 {include file='footer.tpl'}

