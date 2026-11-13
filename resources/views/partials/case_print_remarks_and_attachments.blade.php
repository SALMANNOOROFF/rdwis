{{-- Approval & Remarks Trail (Clean Plain Text Format - No Boxes) --}}
<span class="section-title">{{ $remarksSectionTitle ?? 'Approval & Remarks Trail' }}: ({{ count($remarksList) }})</span>

<div class="remarks-plain-list">
    @php
        $seqCounter = 1;
        $entryNo = 1;
    @endphp

    @forelse($remarksList as $entry)
        @php
            $rawRemarks = (string) ($entry['remarks'] ?? '');
            $hasHtmlLi = $rawRemarks !== '' && strpos($rawRemarks, '<li') !== false;
            $plainTrim = trim(strip_tags($rawRemarks));

            if ($hasHtmlLi) {
                $paraCount = max(1, substr_count($rawRemarks, '<li'));
                $innerLis = preg_replace('/<\/?(ol|ul)[^>]*>/i', '', $rawRemarks);
                $renderedRemarks = '<ol start="' . $seqCounter . '">' . $innerLis . '</ol>';
            } elseif ($plainTrim !== '') {
                $lines = preg_split('/\r\n|\r|\n/', $plainTrim);
                $lis = [];
                foreach ($lines as $line) {
                    $cleanText = trim(preg_replace('/^\d+\.\s*/', '', $line));
                    if ($cleanText !== '') {
                        $lis[] = '<li>' . e($cleanText) . '</li>';
                    }
                }
                if (empty($lis)) {
                    $lis[] = '<li>' . e($plainTrim) . '</li>';
                }
                $paraCount = count($lis);
                $renderedRemarks = '<ol start="' . $seqCounter . '">' . implode('', $lis) . '</ol>';
            } else {
                $paraCount = 1;
                $actionLabel = $entry['action'] ?: 'Processed';
                $renderedRemarks = '<ol start="' . $seqCounter . '"><li><em>' . e($actionLabel) . ' without additional written remarks.</em></li></ol>';
            }
            $seqCounter += $paraCount;
        @endphp

        <div class="remark-text-item">
            <div class="remark-text-head">
                <div>
                    <strong>{{ $entryNo++ }}. {{ $entry['actor'] ?: 'Authorized Officer' }}</strong>
                    @if(!empty($entry['seat']))
                        <span>({{ $entry['seat'] }})</span>
                    @endif
                    @if(!empty($entry['action']))
                        &mdash; <strong>{{ $entry['action'] }}</strong>
                    @endif
                </div>
                <div class="remark-text-date">
                    {{ $entry['timestamp'] ?: '—' }}
                </div>
            </div>
            <div class="remark-text-body">
                {!! $renderedRemarks !!}
            </div>
        </div>
    @empty
        <div style="color: #64748b; font-style: italic; margin-bottom: 12px;">
            No remarks or routing actions recorded yet.
        </div>
    @endforelse
</div>

{{-- Point-Wise Attached Files Register (Clean Text List - No Table Boxes, No View Buttons) --}}
<span class="section-title">{{ $attachmentsSectionTitle ?? 'Attached Documents' }}: ({{ count($enclosuresList) }})</span>

@if(count($enclosuresList) > 0)
    <ol class="enclosure-point-list">
        @foreach($enclosuresList as $file)
            <li>
                <strong>{{ $file['title'] ?: 'Document' }}</strong>
                @if(!empty($file['filename']) && $file['filename'] !== $file['title'])
                    &mdash; File: <span style="font-family: monospace;">{{ $file['filename'] }}</span>
                @elseif(!empty($file['filename']))
                    &mdash; <span style="font-family: monospace;">{{ $file['filename'] }}</span>
                @endif
                @php
                    $metaParts = [];
                    if (!empty($file['source'])) $metaParts[] = $file['source'];
                    if (!empty($file['uploaded_by'])) $metaParts[] = 'Attached by: ' . $file['uploaded_by'];
                    if (!empty($file['meta'])) $metaParts[] = $file['meta'];
                @endphp
                @if(!empty($metaParts))
                    <span style="color: #475569; font-size: 9pt;">({{ implode(' | ', $metaParts) }})</span>
                @endif
                @if(!empty($file['url']))
                    <a href="{{ $file['url'] }}" target="_blank" class="enclosure-inline-link no-print">Open</a>
                @endif
            </li>
        @endforeach
    </ol>

    {{-- Render All Attached Images & PDFs Directly Below in Print Shape --}}
    @php
        $renderableFiles = collect($enclosuresList)->filter(fn($f) => (!empty($f['is_image']) || !empty($f['is_pdf'])) && !empty($f['url']))->values();
    @endphp

    @if($renderableFiles->count() > 0)
        <div class="enclosures-render-wrap">
            @foreach($renderableFiles as $rIdx => $rFile)
                <div class="enclosure-sheet">
                    <div class="enclosure-sheet-header">
                        <div>
                            <strong>Enclosure #{{ $rIdx + 1 }}: {{ $rFile['title'] }}</strong>
                            @if(!empty($rFile['filename']))
                                ({{ $rFile['filename'] }})
                            @endif
                            @if(!empty($rFile['uploaded_by']))
                                &mdash; Attached by: {{ $rFile['uploaded_by'] }}
                            @endif
                        </div>
                        <div>FOR RECORD PURPOSE</div>
                    </div>

                    @if(!empty($rFile['is_image']))
                        <img src="{{ $rFile['url'] }}" class="enclosure-rendered-img" alt="{{ $rFile['title'] }}">
                    @elseif(!empty($rFile['is_pdf']))
                        <div class="pdf-render-container" data-pdf-url="{{ $rFile['url'] }}" id="pdf-container-{{ $rIdx }}">
                            <div class="pdf-loading-msg">Loading attached PDF ({{ $rFile['filename'] }})...</div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
@else
    <div style="color: #64748b; font-style: italic; margin-bottom: 12px;">
        No files attached to this case.
    </div>
@endif
