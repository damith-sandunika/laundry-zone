<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs text-gray-400 mb-1"><a href="{{ route('expenses.index') }}" class="hover:text-blue-600">← Expenses</a></p>
            <h2 class="text-xl font-bold text-gray-800">Edit Expense</h2>
        </div>
    </x-slot>

    <div class="max-w-lg">
        <div class="bg-white rounded-lg border border-gray-100 shadow-sm p-6">
            <form action="{{ route('expenses.update', $expense) }}" method="POST" class="space-y-4">
                @csrf @method('PUT')

                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Expense Category</label>
                    <select name="category" class="w-full text-sm border-gray-200 rounded focus:border-blue-500 focus:ring-blue-500">
                        @foreach($expenseCategories as $cat)
                            <option value="{{ $cat->name }}" {{ $expense->category === $cat->name ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Description</label>
                    <input type="text" name="description" value="{{ old('description', $expense->description) }}" required
                        class="w-full text-sm border-gray-200 rounded focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Amount (Rs.)</label>
                        <input type="number" step="0.01" name="amount" value="{{ old('amount', $expense->amount) }}" required
                            class="w-full text-sm border-gray-200 rounded focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Date</label>
                        <input type="date" name="expense_date" value="{{ old('expense_date', \Carbon\Carbon::parse($expense->expense_date)->format('Y-m-d')) }}" required
                            class="w-full text-sm border-gray-200 rounded focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded transition-colors">
                        Save Changes
                    </button>
                    <a href="{{ route('expenses.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 text-xs font-semibold rounded hover:bg-gray-200 transition-colors">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>


