# Claude Code Project Tracking Wrapper
claude() {
    local repo_name

    # Use the Git repository folder when available, otherwise use the current folder.
    if git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
        repo_name=$(basename "$(git rev-parse --show-toplevel)")
    else
        repo_name=$(basename "$PWD")
    fi

    # Normalize the name: lowercase, underscores for spaces, and no special characters.
    repo_name=$(printf '%s' "$repo_name" | tr '[:upper:] ' '[:lower:]_' | sed 's/[^a-z0-9_-]//g')

    # Append project name to existing OTel attributes, or create a new string.
    if [ -n "${OTEL_RESOURCE_ATTRIBUTES:-}" ]; then
        export OTEL_RESOURCE_ATTRIBUTES="project.name=${repo_name},${OTEL_RESOURCE_ATTRIBUTES}"
    else
        export OTEL_RESOURCE_ATTRIBUTES="project.name=${repo_name}"
    fi

    # Execute the actual Claude Code binary with passed arguments
    command claude "$@"
}
