{assign var=tree value=$commit->GetTree()}

{if $unselect == "summary"}
summary
{else}
<a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=summary">summary</a>
{/if}
|
{if $unselect == "shortlog"}
shortlog
{else}
<a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=shortlog&h={$commit->GetHash()}">shortlog</a>
{/if}
|
{if $unselect == "log"}
log
{else}
<a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=log&h={$commit->GetHash()}">log</a>
{/if}
|
{if $unselect == "commit"}
commit
{else}
<a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=commit&h={$commit->GetHash()}">commit</a>
{/if}
|
{if $unselect == "commitdiff"}
commitdiff
{else}
<a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=commitdiff&h={$commit->GetHash()}">commitdiff</a>
{/if}
|
{if $unselect == "tree"}
tree
{else}
<a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=tree&h={$tree->GetHash()}&hb={$commit->GetHash()}">tree</a>
{/if}
