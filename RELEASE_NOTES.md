0.6.5
- fix bug in previous release (0.6.4)

0.6.4
- attach standard descriptors from parent to child process
  this allows interactive commands executions (like vim, etc.)

0.6.3
- entry script may not be executable, so do not require this

0.6.2
- fix build cli cmd

0.6.1
- fix docs
- fix executing commands
- add option entry_script + cli option --script
- add option style.result.content
- rename option style.help.scope => style.help.content
- replace option result_border => style.result.separator

0.6.0
- each action run in separate process now

0.5.0
- add events
    - beforeRunAction
    - afterRunAction
- configuration changes
- hide some dependencies to init func
    

0.4.0
- fix bugs
    - parse input
- add trace option
- add runtime configuration
- upd ridzhi/smarrt ver 1.0 -> 2.0
