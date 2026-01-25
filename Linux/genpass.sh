# genpass - Shell function to generate a random password of specified length using letters, digits, and symbols.
#
# Built for bash and zsh; uses common utilities and avoids shell-specific expansions.
#
# Usage: genpass LENGTH
# - Uses letters/digits plus symbols: !@#$%^&*-_+=:?
# - Symbols are limited to at most 1/4 the legnth of the password.

genpass() {
    if [[ $# -eq 0 || $1 -le 0 ]]; then
        printf '%s\n' "genpass needs a positive length argument" >&2
        return 1
    fi

    local -i length=$1
    shift

    # sets (put '-' first so tr treats it literally)
    local symbols_set='!-@#$%^&*_+=:?'
    # if you want '_' counted as a "letter" instead of symbol, add it here and remove from symbols_set
    local others_set='A-Za-z0-9_'

    # symbol cap: at most 1/4 of length
    local -i sym_cap=$(( length / 4 ))

    # choose how many symbols to use: anywhere from 0..sym_cap
    # (If you want EXACTLY the cap every time, replace next line with: local -i num_symbols=$sym_cap)
    local -i num_symbols=$(( sym_cap > 0 ? (RANDOM % (sym_cap + 1)) : 0 ))
    local -i num_others=$(( length - num_symbols ))

    # pull bytes from /dev/urandom and filter to desired character sets
    local symbols others combined
    symbols=$(LC_ALL=C tr -dc "$symbols_set" < /dev/urandom | head -c $num_symbols)
    others=$(LC_ALL=C tr -dc "$others_set"   < /dev/urandom | head -c $num_others)
    combined="${others}${symbols}"

    # split into single characters and shuffle (Fisher-Yates) via awk for bash/zsh
    local password
    password=$(
        LC_ALL=C printf '%s' "$combined" \
        | LC_ALL=C fold -w1 \
        | awk 'BEGIN{srand()} {a[NR]=$0} END{for(i=NR;i>0;i--){j=int(rand()*i)+1;tmp=a[i];a[i]=a[j];a[j]=tmp} for(i=1;i<=NR;i++) printf "%s", a[i]; printf "\n"}'
    )

    printf '%s\n' "Generated password:" >&2
    printf '%s\n' "$password"
}
