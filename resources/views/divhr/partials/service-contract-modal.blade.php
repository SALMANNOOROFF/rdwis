{{-- ========================================================================= --}}
{{-- SERVICE CONTRACT SELECTION MODAL                                          --}}
{{-- Opens the official 4-page Service Contract in a dedicated full page / tab --}}
{{-- ========================================================================= --}}

<div id="contractPickerModal" class="fixed inset-0 z-50 flex items-center justify-center p-3 hidden" style="background-color: rgba(15, 23, 42, 0.45);">
  <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-200">
    <div class="bg-slate-100 border-b border-slate-200 px-5 py-3.5 flex justify-between items-center">
      <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-lg bg-sky-100 border border-sky-300 flex items-center justify-center text-sky-700">
          <i class="fas fa-file-signature text-sm"></i>
        </div>
        <h3 class="font-bold text-sm text-slate-800">Select Contract to View</h3>
      </div>
      <button type="button" onclick="document.getElementById('contractPickerModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 cursor-pointer">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <div class="p-5">
      <p class="text-xs text-slate-500 mb-3">Please choose which contract of <strong class="text-slate-800">{{ $emp->emp_name ?? 'this employee' }}</strong> you would like to open as the official Service Contract:</p>
      <div class="space-y-2 max-h-60 overflow-y-auto pr-1" id="contractPickerList">
        @forelse(($contractsHistory ?? collect()) as $idx => $c)
          @php
            $sDate = !empty($c->ctr_startdt) ? \Carbon\Carbon::parse($c->ctr_startdt)->format('d M, Y') : '—';
            $eDate = !empty($c->ctr_enddt) ? \Carbon\Carbon::parse($c->ctr_enddt)->format('d M, Y') : '—';
            $cSal = !empty($c->ctr_salary) ? 'PKR ' . number_format($c->ctr_salary) : '—';
            $cDesig = $c->ctr_jobtitle ?: ($emp->emp_title ?: 'Officer');
          @endphp
          <a href="{{ url('/divhr/employee/' . ($emp->emp_id ?? $id) . '/service-contract/' . $c->ctr_id) }}" 
             target="_blank" 
             onclick="document.getElementById('contractPickerModal').classList.add('hidden')"
             class="w-full text-left p-3 rounded-xl border border-slate-200 hover:border-sky-500 hover:bg-sky-50/50 transition flex items-center justify-between group cursor-pointer block text-decoration-none">
            <div>
              <div class="font-bold text-xs text-slate-800 group-hover:text-sky-700 flex items-center gap-1.5">
                <span>{{ $cDesig }}</span>
                <i class="fas fa-external-link-alt text-[10px] text-sky-500"></i>
              </div>
              <div class="text-[11px] text-slate-500 mt-0.5"><i class="far fa-calendar-alt mr-1"></i> {{ $sDate }} – {{ $eDate }}</div>
            </div>
            <div class="text-right">
              <span class="text-xs font-bold text-emerald-600 block">{{ $cSal }}</span>
              <span class="text-[10px] px-2 py-0.5 rounded-full font-bold {{ $c->status_label === 'Active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $c->status_label }}</span>
            </div>
          </a>
        @empty
          <div class="p-4 text-center text-xs text-slate-400">No contracts available for this employee.</div>
        @endforelse
      </div>
    </div>
    <div class="bg-slate-50 px-5 py-2.5 border-t border-slate-200 text-right">
      <button type="button" onclick="document.getElementById('contractPickerModal').classList.add('hidden')" class="px-4 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-800 border border-slate-300 rounded-lg bg-white cursor-pointer">Cancel</button>
    </div>
  </div>
</div>

<script>
  window.openServiceContractSelector = function() {
    const picker = document.getElementById('contractPickerModal');
    if (picker) picker.classList.remove('hidden');
  };
</script>
