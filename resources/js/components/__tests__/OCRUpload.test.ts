import { mount } from '@vue/test-utils'
import { describe, it, expect, vi } from 'vitest'
import OCRUpload from '../OCRUpload.vue'

// Mock the OCR service
vi.mock('../../services/ocrService', () => {
  return {
    default: {
      initialize: vi.fn(),
      processImage: vi.fn(),
      getAvailableLanguages: vi.fn().mockReturnValue([
        { code: 'ind', name: 'Indonesian', tesseractCode: 'ind+eng', direction: 'ltr' }
      ]),
      getCurrentLanguage: vi.fn().mockReturnValue('ind')
    }
  }
})

describe('OCRUpload', () => {
  it('renders correctly', () => {
    const wrapper = mount(OCRUpload)
    expect(wrapper.exists()).toBe(true)
  })

  it('shows upload area when no image is selected', () => {
    const wrapper = mount(OCRUpload)
    expect(wrapper.find('.border-dashed').exists()).toBe(true)
  })

  it('applies extracted data when "Gunakan Data Ini" button is clicked', async () => {
    const wrapper = mount(OCRUpload, {
      props: {
        modelValue: {
          text: 'Sample OCR text',
          confidence: 95,
          extractedData: {
            amount: 100000,
            date: '2023-01-01',
            referenceNumber: 'REF123'
          }
        }
      }
    })

    // Wait for the component to update with the OCR results
    await wrapper.vm.$nextTick()

    // Find and click the "Gunakan Data Ini" button
    const useDataButton = wrapper.find('button.flex-1.btn-primary')
    expect(useDataButton.exists()).toBe(true)
    
    await useDataButton.trigger('click')
    
    // Check if the dataExtracted event was emitted
    expect(wrapper.emitted('dataExtracted')).toBeTruthy()
    expect(wrapper.emitted('dataExtracted')![0]).toEqual([{
      amount: 100000,
      date: '2023-01-01',
      referenceNumber: 'REF123'
    }])
  })
})