---
name: push
argument-hint: [commit-message]
---

Push all project changes to GitHub:

1. Run `git status`; if the repository is clean, stop and notify the developer immediately.
2. Run `git add .` to stage all modified files and directories.
3. Determine message: if `$ARGUMENTS` is provided, use it; otherwise, auto-generate a professional English `Conventional Commit` message.
4. Run `git commit -m "[message]"` using the determined manual or auto-generated commit message.
5. Run `git branch --show-current` to detect the currently active branch name.
6. Run `git push origin [branch-name]` to push the changes directly to the remote repository.
7. Output a concise success summary showing only the active branch name and the final commit message.