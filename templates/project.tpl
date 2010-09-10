{*
 *  project.tpl
 *  gitphp: A PHP git repository browser
 *  Component: Project summary template
 *
 *  Copyright (C) 2009 Christopher Han <xiphux@gmail.com>
 *}

 {include file='header.tpl'}

 {* Nav *}
 <div class="page_nav">
   {include file='project_header.tpl' unselect='summary' commit=$head}
   <br /><br />
 </div>

 {include file='title.tpl'}

 {* Project brief *}
 <table cellspacing="0">
   <tr><td>description</td><td>{$project->GetDescription()}</td></tr>
   <tr><td>owner</td><td>{$project->GetOwner()}</td></tr>
   <tr><td>last change</td><td>{$head->GetCommitterEpoch()|date_format:"%a, %d %b %Y %H:%M:%S %z"}</td></tr>
   {if $project->GetCloneUrl()}
     <tr><td>clone url</td><td>{$project->GetCloneUrl()}</td></tr>
   {/if}
   {if $project->GetPushUrl()}
     <tr><td>push url</td><td>{$project->GetPushUrl()}</td></tr>
   {/if}
 </table>

 {include file='title.tpl' target='shortlog'}
 
 <table cellspacing="0">
   {include file='shortlog_list.tpl' revlist=$revlist}
   {if $hasmorerevs}
     <tr class="light">
       <td><a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=shortlog">...</a></td>
     </tr>
   {/if}
 </table>
 {if $taglist}
   {* Tags *}
   {include file='title.tpl' target='tags'}
   <table cellspacing="0">
     {include file='tag_list.tpl' taglist=$taglist}
     {if $hasmoretags}
       <tr class="light">
         <td><a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=tags">...</a></td>
       </tr>
     {/if}
   </table>


   
 {/if}
 {if $headlist}
   {* Heads *}

   {include file='title.tpl' target='heads'}

   <table cellspacing="0">
     {include file='head_list.tpl' headlist=$headlist}
     {if $hasmoreheads}
       <tr class="light">
         <td><a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=heads">...</a></td>
       </tr>
     {/if}
   </table>

 {/if}

 {include file='footer.tpl'}

