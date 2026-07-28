#!/usr/bin/env bash

set -Eeuo pipefail

readonly SOURCE_DIRECTORY="docs/diagrams/mermaid"
readonly OUTPUT_DIRECTORY="docs/diagrams/generated"
readonly IMAGE="${MERMAID_IMAGE:-minlag/mermaid-cli:latest}"

if ! compgen -G "${SOURCE_DIRECTORY}/*.mmd" >/dev/null; then
    echo "No Mermaid source files were found in ${SOURCE_DIRECTORY}." >&2
    exit 1
fi

mkdir -p "${OUTPUT_DIRECTORY}"

for source_file in "${SOURCE_DIRECTORY}"/*.mmd; do
    file_name="$(basename "${source_file}" .mmd)"
    output_file="${OUTPUT_DIRECTORY}/${file_name}.svg"
    temporary_output_file="${OUTPUT_DIRECTORY}/${file_name}.tmp.svg"

    if ! docker run \
        --rm \
        --user "$(id -u):$(id -g)" \
        --volume "${PWD}:/data" \
        --workdir /data \
        "${IMAGE}" \
        --input "${source_file}" \
        --output "${temporary_output_file}" \
        --outputFormat svg; then
        echo "The diagram ${source_file} could not be rendered with Mermaid CLI." >&2
        rm -f "${temporary_output_file}"
        exit 1
    fi

    if [[ ! -s "${temporary_output_file}" ]]; then
        echo "The diagram ${source_file} could not be rendered because Mermaid CLI returned an empty response." >&2
        rm -f "${temporary_output_file}"
        exit 1
    fi

    mv "${temporary_output_file}" "${output_file}"
    echo "Generated ${output_file}"
done
