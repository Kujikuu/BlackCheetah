# 🧪 Test Suite Documentation

Welcome to the BlackCheetah Validation Test Suite!

---

## 📊 Overview

**Total Tests:** 215+  
**Test Files:** 11  
**Execution Time:** ~4-5 seconds  
**Coverage:** 100% validation + 23% components  

---

## 📁 Test Structure

```
resources/ts/
├── composables/__tests__/
│   ├── useValidationRules.test.ts (50+ tests)
│   └── useFormValidation.test.ts (8+ tests)
├── utils/__tests__/
│   └── formErrorMapper.test.ts (15+ tests)
└── components/dialogs/__tests__/
    ├── EditLeadDialog.test.ts (10+ tests)
    ├── AddDataDialog.test.ts (12+ tests)
    ├── CardAddEditDialog.test.ts (15+ tests)
    ├── AddStaffDialog.test.ts (20+ tests)
    ├── ImportLeadsDialog.test.ts (15+ tests)
    ├── AddEditRoleDialog.test.ts (25+ tests)
    ├── AddFranchiseeDialog.test.ts (25+ tests)
    └── MarkCompletedRoyaltyDialog.test.ts (20+ tests)
```

---

## 🚀 Running Tests

### All Tests
```bash
npm run test
```

### Specific Test Categories
```bash
# Unit tests only
npm run test:unit

# Component tests only
npm run test:component

# Validation tests only
npm run test:validation

# Dialog tests only
npm run test:dialogs
```

### Watch Mode
```bash
npm run test:watch
```

### UI Mode
```bash
npm run test:ui
# Opens at http://localhost:51204/__vitest__/
```

### Coverage
```bash
npm run test:coverage
# Generates report in ./coverage/
```

---

## 📚 Test Categories

### 1. Unit Tests (73 tests)

#### useValidationRules.test.ts (50+ tests)
Tests all validation rules:
- ✅ required, email, phone
- ✅ minLength, maxLength
- ✅ numeric, min, max
- ✅ date, url
- ✅ file, fileSize, fileType
- ✅ inArray, string

#### formErrorMapper.test.ts (15+ tests)
Tests error mapping:
- ✅ snake_case to camelCase
- ✅ Error joining
- ✅ Error clearing

#### useFormValidation.test.ts (8+ tests)
Tests form composable:
- ✅ Backend error setting
- ✅ Error clearing
- ✅ Integration

---

### 2. Component Tests (142+ tests)

#### Simple Forms
- **EditLeadDialog.test.ts** - Standard multi-field form

#### Conditional Forms
- **AddDataDialog.test.ts** - Sales vs Expense validation

#### Complex Formats
- **CardAddEditDialog.test.ts** - Credit card validation

#### Multi-Field Forms
- **AddStaffDialog.test.ts** - 12+ field form

#### File Upload
- **ImportLeadsDialog.test.ts** - CSV import validation

#### Permission Matrix
- **AddEditRoleDialog.test.ts** - Checkbox matrix

#### Multi-Step
- **AddFranchiseeDialog.test.ts** - 3-step wizard

#### Mixed Types
- **MarkCompletedRoyaltyDialog.test.ts** - Numeric + date + file

---

## 📝 Writing New Tests

### Test Template

```typescript
import { describe, it, expect, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import YourDialog from '../YourDialog.vue'
import { createVuetify } from 'vuetify'

const vuetify = createVuetify()

describe('YourDialog.vue', () => {
  let wrapper: any

  beforeEach(() => {
    wrapper = mount(YourDialog, {
      props: { isDialogVisible: true },
      global: {
        plugins: [vuetify],
        stubs: {
          DialogCloseBtn: true,
          AppTextField: true,
        },
      },
    })
  })

  it('should validate required fields', async () => {
    const result = await wrapper.vm.formRef.validate()
    expect(result.valid).toBe(false)
  })
})
```

### Test Best Practices

1. **Descriptive Names** - `it('should validate email format', ...)`
2. **Arrange-Act-Assert** - Clear test structure
3. **One Assertion Focus** - Test one thing per test
4. **Mock External Deps** - Keep tests isolated
5. **Test User Behavior** - Not implementation details

---

## 🎯 What to Test

### Always Test
✅ Required field validation  
✅ Format validation (email, phone, etc.)  
✅ Form submission with valid/invalid data  
✅ Backend error mapping  
✅ Error clearing on input  

### Often Test
✅ Custom validation rules  
✅ Conditional logic  
✅ File upload restrictions  
✅ Multi-step navigation  

### Sometimes Test
✅ Edge cases  
✅ Boundary conditions  
✅ Error scenarios  
✅ Integration points  

---

## 🐛 Debugging Tests

### View Verbose Output
```bash
npm run test -- --reporter=verbose
```

### Run Single Test
```typescript
it.only('should test this', () => {
  // Only this test runs
})
```

### Debug with Console
```typescript
it('should debug', () => {
  console.log(wrapper.vm.formData)
  expect(something).toBe(true)
})
```

### Use Debugger
```typescript
it('should debug with breakpoint', () => {
  debugger
  expect(something).toBe(true)
})
// Run: npx vitest --inspect
```

---

## 📊 Coverage Goals

### Current Coverage
- ✅ Validation Rules: 100%
- ✅ Error Mapping: 100%
- ✅ Form Validation: 100%
- 🎯 Components: 23% (8/35)

### Recommended Coverage
- ✅ Validation Rules: 100%
- ✅ Error Mapping: 100%
- ✅ Form Validation: 100%
- 🎯 Components: 50% (17/35)

---

## 🎯 Adding More Tests

### Priority List (Next 8 Dialogs)
1. CreateTaskDialog - Use EditLeadDialog pattern
2. CreatePropertyDialog - Use AddStaffDialog pattern
3. AddNoteDialog - Use AddStaffDialog pattern
4. AddDocumentModal - Use ImportLeadsDialog pattern
5. AddReviewDialog - Use EditLeadDialog pattern
6. EditUnitDialog - Use EditLeadDialog pattern
7. AddEditAddressDialog - Use AddStaffDialog pattern
8. UserInfoEditDialog - Use AddStaffDialog pattern

**Estimated Time:** 15-20 mins per dialog = 3-4 hours total

---

## ✅ Test Checklist

Before Committing:
- [ ] All tests pass (`npm run test`)
- [ ] No linter errors (`npm run lint`)
- [ ] No type errors (`npm run typecheck`)
- [ ] New features have tests
- [ ] Tests are documented
- [ ] Coverage maintained or improved

---

## 📚 Resources

### Documentation
- `/AUTOMATED_TESTING_GUIDE.md` - Complete guide
- `/TEST_COVERAGE_REPORT.md` - Coverage analysis
- `/VALIDATION_GUIDE.md` - Implementation guide

### External Resources
- [Vitest Docs](https://vitest.dev/)
- [Vue Test Utils](https://test-utils.vuejs.org/)
- [Testing Best Practices](https://testing-library.com/docs/queries/about)

---

## 🎉 Happy Testing!

**Questions?** Check `/AUTOMATED_TESTING_GUIDE.md`  
**Need Help?** Review existing test files for patterns  
**New Feature?** Add tests first (TDD)!  

---

*Last Updated: October 25, 2025*

