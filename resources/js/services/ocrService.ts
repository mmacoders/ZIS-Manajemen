import Tesseract from 'tesseract.js'

export interface OCRLanguageConfig {
  code: string
  name: string
  tesseractCode: string
  direction: 'ltr' | 'rtl'
  patterns: {
    numbers: RegExp
    currency: RegExp
    date: RegExp
    phone: RegExp
    email: RegExp
  }
  formatters: {
    currency: (amount: number) => string
    date: (date: string) => string
    phone: (phone: string) => string
  }
}

export interface MultiLanguageOCRResult extends OCRResult {
  detectedLanguage: string
  languageConfidence: number
  alternativeResults?: {
    language: string
    result: OCRResult
    confidence: number
  }[]
}

export interface OCRBatchResult {
  success: OCRResult[]
  failed: { file: File; error: string }[]
  totalProcessed: number
  successCount: number
  failedCount: number
}

export interface DocumentTemplate {
  name: string
  type: 'identity' | 'transaction' | 'general' | 'bank_statement' | 'receipt'
  patterns: {
    required: string[]
    optional: string[]
  }
  validation: {
    minConfidence: number
    requiredFields: string[]
  }
}

export interface OCRResult {
  text: string
  confidence: number
  extractedData: {
    // Transaction data
    amount?: number
    date?: string
    referenceNumber?: string
    bankName?: string
    accountNumber?: string
    donorName?: string
    // Identity document data
    nik?: string
    name?: string
    birthPlace?: string
    birthDate?: string
    gender?: string
    address?: string
    rt?: string
    rw?: string
    kelurahan?: string
    kecamatan?: string
    religion?: string
    maritalStatus?: string
    occupation?: string
    nationality?: string
    province?: string
    city?: string
    postalCode?: string
    phoneNumber?: string
    email?: string
  }
}

export interface OCRProgress {
  status: string
  progress: number
}

export class OCRService {
  private static instance: OCRService
  private worker: Tesseract.Worker | null = null
  private documentTemplates: Map<string, DocumentTemplate> = new Map()
  private languageConfigs: Map<string, OCRLanguageConfig> = new Map()
  private currentLanguage: string = 'ind'

  private constructor() {
    this.initializeTemplates()
    this.initializeLanguageConfigs()
  }

  public static getInstance(): OCRService {
    if (!OCRService.instance) {
      OCRService.instance = new OCRService()
    }
    return OCRService.instance
  }

  /**
   * Initialize document templates for common forms
   */
  private initializeTemplates(): void {
    // Indonesian ID Card (KTP) Template
    this.documentTemplates.set('ktp', {
      name: 'Kartu Tanda Penduduk (KTP)',
      type: 'identity',
      patterns: {
        required: ['nik', 'name', 'address'],
        optional: ['birthPlace', 'birthDate', 'gender', 'religion', 'maritalStatus']
      },
      validation: {
        minConfidence: 75,
        requiredFields: ['nik', 'name']
      }
    })

    // Bank Statement Template
    this.documentTemplates.set('bank_statement', {
      name: 'Rekening Koran Bank',
      type: 'bank_statement',
      patterns: {
        required: ['bankName', 'accountNumber', 'amount'],
        optional: ['date', 'referenceNumber', 'description']
      },
      validation: {
        minConfidence: 80,
        requiredFields: ['bankName', 'amount']
      }
    })

    // Transaction Receipt Template
    this.documentTemplates.set('receipt', {
      name: 'Bukti Transfer/Kuitansi',
      type: 'transaction',
      patterns: {
        required: ['amount', 'date'],
        optional: ['referenceNumber', 'bankName', 'donorName']
      },
      validation: {
        minConfidence: 70,
        requiredFields: ['amount']
      }
    })

    // General Document Template
    this.documentTemplates.set('general', {
      name: 'Dokumen Umum',
      type: 'general',
      patterns: {
        required: [],
        optional: ['name', 'amount', 'date', 'referenceNumber']
      },
      validation: {
        minConfidence: 60,
        requiredFields: []
      }
    })
  }

  /**
   * Initialize language configurations
   */
  private initializeLanguageConfigs(): void {
    // Indonesian Language Configuration
    this.languageConfigs.set('ind', {
      code: 'ind',
      name: 'Bahasa Indonesia',
      tesseractCode: 'ind+eng',
      direction: 'ltr',
      patterns: {
        numbers: /[0-9]/g,
        currency: /Rp\.?\s*([0-9]{1,3}(?:[.,][0-9]{3})*(?:[.,][0-9]{2})?)/g,
        date: /(\d{1,2}[\-\/\.])\d{1,2}[\-\/\.]\d{4}/g,
        phone: /^(\+62|62|0)[\d\-\s]{8,13}$/,
        email: /[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/g
      },
      formatters: {
        currency: (amount: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(amount),
        date: (date: string) => new Date(date).toLocaleDateString('id-ID'),
        phone: (phone: string) => phone.startsWith('0') ? '+62' + phone.substring(1) : phone
      }
    })

    // Arabic Language Configuration
    this.languageConfigs.set('ara', {
      code: 'ara',
      name: 'Bahasa Arab',
      tesseractCode: 'ara+eng',
      direction: 'rtl',
      patterns: {
        numbers: /[٠-٩0-9]/g,
        currency: /(ريال|درهم|دينار|جنيه)\s*([٠-٩0-9]{1,}(?:[.,][٠-٩0-9]{3})*)/g,
        date: /([٠-٩0-9]{1,2}[\/\-][٠-٩0-9]{1,2}[\/\-][٠-٩0-9]{4})/g,
        phone: /^(\+966|966|05)[٠-٩0-9\-\s]{8,12}$/,
        email: /[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/g
      },
      formatters: {
        currency: (amount: number) => new Intl.NumberFormat('ar-SA', { style: 'currency', currency: 'SAR' }).format(amount),
        date: (date: string) => new Date(date).toLocaleDateString('ar-SA'),
        phone: (phone: string) => phone.startsWith('05') ? '+966' + phone.substring(1) : phone
      }
    })

    // English Language Configuration
    this.languageConfigs.set('eng', {
      code: 'eng',
      name: 'Bahasa Inggris',
      tesseractCode: 'eng',
      direction: 'ltr',
      patterns: {
        numbers: /[0-9]/g,
        currency: /\$\s*([0-9]{1,3}(?:,[0-9]{3})*(?:\.[0-9]{2})?)|([0-9]{1,3}(?:,[0-9]{3})*(?:\.[0-9]{2})?)\s*USD/g,
        date: /(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{4})|(\d{4}[\/\-]\d{1,2}[\/\-]\d{1,2})/g,
        phone: /^(\+1|1)?[\s\-\.]?\(?[0-9]{3}\)?[\s\-\.]?[0-9]{3}[\s\-\.]?[0-9]{4}$/,
        email: /[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/g
      },
      formatters: {
        currency: (amount: number) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount),
        date: (date: string) => new Date(date).toLocaleDateString('en-US'),
        phone: (phone: string) => phone.replace(/\D/g, '').replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3')
      }
    })
  }

  /**
   * Initialize OCR worker
   */
  public async initialize(language: string = 'ind'): Promise<void> {
    if (this.worker && this.currentLanguage === language) {
      return
    }

    // Terminate existing worker if language changed
    if (this.worker && this.currentLanguage !== language) {
      await this.worker.terminate()
      this.worker = null
    }

    const langConfig = this.languageConfigs.get(language)
    const tesseractLang = langConfig ? langConfig.tesseractCode : 'ind+eng'
    
    this.worker = await Tesseract.createWorker(tesseractLang, 1, {
      logger: m => {
        // Optional: Add logger for debugging
        // console.log(m)
      }
    })
    
    this.currentLanguage = language
  }

  /**
   * Set OCR language
   */
  public async setLanguage(language: string): Promise<void> {
    if (this.languageConfigs.has(language)) {
      await this.initialize(language)
    } else {
      throw new Error(`Unsupported language: ${language}`)
    }
  }

  /**
   * Get available languages
   */
  public getAvailableLanguages(): OCRLanguageConfig[] {
    return Array.from(this.languageConfigs.values())
  }

  /**
   * Get current language
   */
  public getCurrentLanguage(): string {
    return this.currentLanguage
  }

  /**
   * Auto-detect document language
   */
  public async detectLanguage(imageFile: File | string): Promise<{ language: string; confidence: number }> {
    const languages = ['ind', 'ara', 'eng']
    const results: { language: string; confidence: number }[] = []

    for (const lang of languages) {
      try {
        await this.initialize(lang)
        const result = await this.worker!.recognize(imageFile, {
          rectangle: undefined
        })
        
        results.push({
          language: lang,
          confidence: result.data.confidence
        })
      } catch (error) {
        console.warn(`Language detection failed for ${lang}:`, error)
      }
    }

    // Return language with highest confidence
    results.sort((a, b) => b.confidence - a.confidence)
    return results[0] || { language: 'ind', confidence: 0 }
  }

  /**
   * Process image with multi-language support
   */
  public async processImageMultiLanguage(
    imageFile: File | string,
    languages: string[] = ['ind', 'ara', 'eng'],
    onProgress?: (progress: OCRProgress) => void,
    documentType: 'transaction' | 'identity' | 'general' = 'general'
  ): Promise<MultiLanguageOCRResult> {
    const results: { language: string; result: OCRResult; confidence: number }[] = []
    let bestResult: OCRResult | null = null
    let bestLanguage = 'ind'
    let bestConfidence = 0

    for (let i = 0; i < languages.length; i++) {
      const language = languages[i]
      
      try {
        onProgress?.({
          status: `Processing with ${this.languageConfigs.get(language)?.name || language}...`,
          progress: (i / languages.length) * 100
        })

        await this.initialize(language)
        const result = await this.processImage(imageFile, undefined, documentType)
        
        results.push({
          language,
          result,
          confidence: result.confidence
        })

        if (result.confidence > bestConfidence) {
          bestResult = result
          bestLanguage = language
          bestConfidence = result.confidence
        }
      } catch (error) {
        console.warn(`Processing failed for language ${language}:`, error)
      }
    }

    onProgress?.({
      status: 'Processing complete',
      progress: 100
    })

    if (!bestResult) {
      throw new Error('Failed to process image with any supported language')
    }

    return {
      ...bestResult,
      detectedLanguage: bestLanguage,
      languageConfidence: bestConfidence,
      alternativeResults: results.filter(r => r.language !== bestLanguage)
    }
  }

  /**
   * Get available document templates
   */
  public getDocumentTemplates(): DocumentTemplate[] {
    return Array.from(this.documentTemplates.values())
  }

  /**
   * Get specific document template
   */
  public getTemplate(templateName: string): DocumentTemplate | undefined {
    return this.documentTemplates.get(templateName)
  }

  /**
   * Process multiple images in batch
   */
  public async processBatch(
    files: File[],
    onProgress?: (processed: number, total: number, currentFile: string) => void,
    documentType: 'transaction' | 'identity' | 'general' = 'general',
    templateName?: string
  ): Promise<OCRBatchResult> {
    if (!this.worker) {
      await this.initialize()
    }

    const results: OCRBatchResult = {
      success: [],
      failed: [],
      totalProcessed: 0,
      successCount: 0,
      failedCount: 0
    }

    const template = templateName ? this.getTemplate(templateName) : undefined

    for (let i = 0; i < files.length; i++) {
      const file = files[i]
      
      try {
        onProgress?.(i, files.length, file.name)
        
        const result = await this.processImage(
          file,
          undefined,
          documentType,
          template
        )
        
        // Validate result based on template if provided
        if (template && !this.validateResult(result, template)) {
          results.failed.push({
            file,
            error: `Document does not match template requirements for ${template.name}`
          })
          results.failedCount++
        } else {
          results.success.push(result)
          results.successCount++
        }
      } catch (error) {
        results.failed.push({
          file,
          error: error instanceof Error ? error.message : 'Unknown error'
        })
        results.failedCount++
      }
      
      results.totalProcessed++
    }

    onProgress?.(files.length, files.length, 'Completed')
    return results
  }

  /**
   * Validate OCR result against template
   */
  private validateResult(result: OCRResult, template: DocumentTemplate): boolean {
    // Check minimum confidence
    if (result.confidence < template.validation.minConfidence) {
      return false
    }

    // Check required fields
    for (const field of template.validation.requiredFields) {
      if (!result.extractedData[field as keyof typeof result.extractedData]) {
        return false
      }
    }

    return true
  }

  /**
   * Process image and extract text using OCR
   */
  public async processImage(
    imageFile: File | string, 
    onProgress?: (progress: OCRProgress) => void,
    documentType: 'transaction' | 'identity' | 'general' = 'general',
    template?: DocumentTemplate
  ): Promise<OCRResult> {
    if (!this.worker) {
      await this.initialize()
    }

    try {
      const result = await this.worker!.recognize(imageFile, {
        rectangle: undefined, // Process entire image
      })

      const ocrResult: OCRResult = {
        text: result.data.text,
        confidence: result.data.confidence,
        extractedData: this.extractStructuredData(result.data.text, documentType)
      }

      return ocrResult
    } catch (error) {
      console.error('OCR processing error:', error)
      throw new Error('Failed to process image')
    }
  }

  /**
   * Extract structured data from OCR text using language-specific patterns
   */
  private extractStructuredData(text: string, documentType: string = 'general', language?: string): OCRResult['extractedData'] {
    const extractedData: OCRResult['extractedData'] = {}
    const cleanText = text.replace(/\s+/g, ' ').trim()
    const currentLang = language || this.currentLanguage
    const langConfig = this.languageConfigs.get(currentLang)

    // Apply language-specific extraction
    if (langConfig) {
      extractedData.phoneNumber = this.extractWithLanguagePattern(cleanText, langConfig.patterns.phone)
      extractedData.email = this.extractWithLanguagePattern(cleanText, langConfig.patterns.email)
      
      // Extract and format currency
      const currencyMatch = cleanText.match(langConfig.patterns.currency)
      if (currencyMatch) {
        const amount = this.parseAmount(currencyMatch[0], currentLang)
        if (amount) {
          extractedData.amount = amount
        }
      }
      
      // Extract and format date
      const dateMatch = cleanText.match(langConfig.patterns.date)
      if (dateMatch) {
        extractedData.date = this.standardizeDate(dateMatch[0], currentLang)
      }
    }

    // Apply document type specific extraction with language support
    switch (documentType) {
      case 'identity':
        return { ...extractedData, ...this.extractIdentityDataMultiLang(cleanText, currentLang) }
      case 'transaction':
        return { ...extractedData, ...this.extractTransactionDataMultiLang(cleanText, currentLang) }
      default:
        return { ...extractedData, ...this.extractGeneralDataMultiLang(cleanText, currentLang) }
    }
  }

  /**
   * Extract with language-specific pattern
   */
  private extractWithLanguagePattern(text: string, pattern: RegExp): string | undefined {
    const match = text.match(pattern)
    return match ? match[0] : undefined
  }

  /**
   * Parse amount based on language
   */
  private parseAmount(amountStr: string, language: string): number | undefined {
    const langConfig = this.languageConfigs.get(language)
    if (!langConfig) return undefined

    switch (language) {
      case 'ara':
        // Convert Arabic-Indic numerals to regular numbers
        const arabicAmount = amountStr.replace(/[٠-٩]/g, (d) => 
          String.fromCharCode(d.charCodeAt(0) - '٠'.charCodeAt(0) + '0'.charCodeAt(0))
        )
        return parseFloat(arabicAmount.replace(/[^0-9.]/g, ''))
      
      case 'eng':
        return parseFloat(amountStr.replace(/[^0-9.]/g, ''))
      
      case 'ind':
      default:
        return parseFloat(amountStr.replace(/[^0-9]/g, ''))
    }
  }

  /**
   * Multi-language identity data extraction
   */
  private extractIdentityDataMultiLang(text: string, language: string): OCRResult['extractedData'] {
    const extractedData: OCRResult['extractedData'] = {}
    const cleanText = text.toUpperCase()

    switch (language) {
      case 'ara':
        return this.extractArabicIdentityData(cleanText)
      case 'eng':
        return this.extractEnglishIdentityData(cleanText)
      case 'ind':
      default:
        return this.extractIdentityData(cleanText)
    }
  }

  /**
   * Multi-language transaction data extraction
   */
  private extractTransactionDataMultiLang(text: string, language: string): OCRResult['extractedData'] {
    const extractedData: OCRResult['extractedData'] = {}
    const cleanText = text.replace(/\s+/g, ' ').trim()

    switch (language) {
      case 'ara':
        return this.extractArabicTransactionData(cleanText)
      case 'eng':
        return this.extractEnglishTransactionData(cleanText)
      case 'ind':
      default:
        return this.extractTransactionData(cleanText)
    }
  }

  /**
   * Multi-language general data extraction
   */
  private extractGeneralDataMultiLang(text: string, language: string): OCRResult['extractedData'] {
    const identityData = this.extractIdentityDataMultiLang(text, language)
    const transactionData = this.extractTransactionDataMultiLang(text, language)
    
    return {
      ...transactionData,
      ...identityData
    }
  }

  /**
   * Extract Arabic identity data
   */
  private extractArabicIdentityData(text: string): OCRResult['extractedData'] {
    const extractedData: OCRResult['extractedData'] = {}

    // Arabic ID patterns (Saudi ID as example)
    const idPattern = /(رقم الهوية|ID\s*NUMBER)[\s:]*([0-9٠-٩]{10})/i
    const idMatch = text.match(idPattern)
    if (idMatch) {
      extractedData.nik = this.convertArabicNumerals(idMatch[2])
    }

    // Arabic name pattern
    const namePattern = /(الاسم|NAME)[\s:]*([\u0600-\u06FF\s]{2,50})/i
    const nameMatch = text.match(namePattern)
    if (nameMatch) {
      extractedData.name = nameMatch[2].trim()
    }

    // Arabic address pattern
    const addressPattern = /(العنوان|ADDRESS)[\s:]*([\u0600-\u06FF0-9\s\.\,]{10,100})/i
    const addressMatch = text.match(addressPattern)
    if (addressMatch) {
      extractedData.address = addressMatch[2].trim()
    }

    return extractedData
  }

  /**
   * Extract English identity data
   */
  private extractEnglishIdentityData(text: string): OCRResult['extractedData'] {
    const extractedData: OCRResult['extractedData'] = {}

    // SSN pattern (US)
    const ssnPattern = /(SSN|SOCIAL\s*SECURITY)[\s:]*([0-9]{3}[\-\s]?[0-9]{2}[\-\s]?[0-9]{4})/i
    const ssnMatch = text.match(ssnPattern)
    if (ssnMatch) {
      extractedData.nik = ssnMatch[2].replace(/[\-\s]/g, '')
    }

    // Driver's License pattern
    const dlPattern = /(DRIVER[\s']?S?\s*LIC|DL)[\s:]*([A-Z0-9]{6,12})/i
    const dlMatch = text.match(dlPattern)
    if (dlMatch && !extractedData.nik) {
      extractedData.nik = dlMatch[2]
    }

    // English name pattern
    const namePattern = /(FULL\s*NAME|NAME)[\s:]*([A-Z\s]{2,50})/i
    const nameMatch = text.match(namePattern)
    if (nameMatch) {
      extractedData.name = this.capitalizeWords(nameMatch[2].trim())
    }

    // Address pattern
    const addressPattern = /(ADDRESS)[\s:]*([A-Z0-9\s\.\,]{10,100})/i
    const addressMatch = text.match(addressPattern)
    if (addressMatch) {
      extractedData.address = this.capitalizeWords(addressMatch[2].trim())
    }

    return extractedData
  }

  /**
   * Extract Arabic transaction data
   */
  private extractArabicTransactionData(text: string): OCRResult['extractedData'] {
    const extractedData: OCRResult['extractedData'] = {}

    // Arabic currency patterns
    const amountPattern = /(ريال|درهم)\s*([0-9٠-٩]{1,}(?:[.,][0-9٠-٩]{3})*)/i
    const amountMatch = text.match(amountPattern)
    if (amountMatch) {
      extractedData.amount = parseFloat(this.convertArabicNumerals(amountMatch[2]))
    }

    // Arabic date pattern
    const datePattern = /([0-9٠-٩]{1,2}[\/\-][0-9٠-٩]{1,2}[\/\-][0-9٠-٩]{4})/i
    const dateMatch = text.match(datePattern)
    if (dateMatch) {
      extractedData.date = this.standardizeDate(this.convertArabicNumerals(dateMatch[1]))
    }

    // Arabic bank name pattern
    const bankPattern = /(مصرف|BANK)\s*([\u0600-\u06FFA-Z\s]{3,20})/i
    const bankMatch = text.match(bankPattern)
    if (bankMatch) {
      extractedData.bankName = bankMatch[2].trim()
    }

    return extractedData
  }

  /**
   * Extract English transaction data
   */
  private extractEnglishTransactionData(text: string): OCRResult['extractedData'] {
    const extractedData: OCRResult['extractedData'] = {}

    // USD amount patterns
    const amountPatterns = [
      /\$\s*([0-9]{1,3}(?:,[0-9]{3})*(?:\.[0-9]{2})?)/i,
      /([0-9]{1,3}(?:,[0-9]{3})*(?:\.[0-9]{2})?)\s*USD/i,
      /(AMOUNT|TOTAL)[\s:]*\$?\s*([0-9]{1,3}(?:,[0-9]{3})*)/i
    ]

    for (const pattern of amountPatterns) {
      const match = text.match(pattern)
      if (match) {
        const amountStr = match[match.length - 1]
        const amount = parseFloat(amountStr.replace(/[,]/g, ''))
        if (amount > 0) {
          extractedData.amount = amount
          break
        }
      }
    }

    // English date patterns
    const datePatterns = [
      /(\d{1,2}\/\d{1,2}\/\d{4})/,
      /(\d{4}\-\d{1,2}\-\d{1,2})/,
      /(DATE)[\s:]*([0-9\/\-]{8,10})/i
    ]

    for (const pattern of datePatterns) {
      const match = text.match(pattern)
      if (match) {
        extractedData.date = this.standardizeDate(match[match.length - 1])
        break
      }
    }

    // Bank name patterns
    const bankPatterns = [
      /\b(BANK\s+OF\s+[A-Z]+|[A-Z]+\s+BANK|WELLS\s+FARGO|CHASE|CITIBANK|BANK\s+OF\s+AMERICA)\b/i,
      /(BANK)[\s:]*([A-Z\s]{3,20})/i
    ]

    for (const pattern of bankPatterns) {
      const match = text.match(pattern)
      if (match) {
        extractedData.bankName = match[0].trim()
        break
      }
    }

    return extractedData
  }

  /**
   * Convert Arabic-Indic numerals to regular numbers
   */
  private convertArabicNumerals(text: string): string {
    return text.replace(/[٠-٩]/g, (d) => 
      String.fromCharCode(d.charCodeAt(0) - '٠'.charCodeAt(0) + '0'.charCodeAt(0))
    )
  }

  /**
   * Extract identity data (Indonesian KTP)
   */
  private extractIdentityData(text: string): OCRResult['extractedData'] {
    const extractedData: OCRResult['extractedData'] = {}
    const cleanText = text.toUpperCase()

    // NIK Pattern (16 digits)
    const nikPattern = /(?:NIK|NOMOR|NO|N\.I\.K)[\s:]*([0-9]{16})/i
    const nikMatch = cleanText.match(nikPattern)
    if (nikMatch) {
      extractedData.nik = nikMatch[1]
    }

    // Name Pattern
    const namePatterns = [
      /(?:NAMA|NAME)[\s:]*([A-Z\s]{2,50})/,
      /(?:^|\n)([A-Z][A-Z\s]{2,30})(?=\n|$)/
    ]
    for (const pattern of namePatterns) {
      const match = cleanText.match(pattern)
      if (match && !match[1].includes('REPUBLIK') && !match[1].includes('INDONESIA')) {
        extractedData.name = this.capitalizeWords(match[1].trim())
        break
      }
    }

    // Birth Place and Date
    const birthPattern = /(?:TEMPAT.*?LAHIR|TTL)[\s:]*([A-Z\s]+),?[\s]*([0-9]{1,2}[\-\/][0-9]{1,2}[\-\/][0-9]{4})/i
    const birthMatch = cleanText.match(birthPattern)
    if (birthMatch) {
      extractedData.birthPlace = this.capitalizeWords(birthMatch[1].trim())
      extractedData.birthDate = this.standardizeDate(birthMatch[2])
    }

    // Gender
    const genderPattern = /(?:JENIS.*?KELAMIN|KELAMIN)[\s:]*([A-Z]+)/i
    const genderMatch = cleanText.match(genderPattern)
    if (genderMatch) {
      const gender = genderMatch[1].trim()
      extractedData.gender = gender.includes('LAKI') ? 'Laki-laki' : gender.includes('PEREMPUAN') ? 'Perempuan' : gender
    }

    // Address
    const addressPattern = /(?:ALAMAT)[\s:]*([A-Z0-9\s\.\/,]{10,100})/i
    const addressMatch = cleanText.match(addressPattern)
    if (addressMatch) {
      extractedData.address = this.capitalizeWords(addressMatch[1].trim())
    }

    // RT/RW
    const rtRwPattern = /RT[\s\/]*([0-9]{1,3})[\s\/]*RW[\s\/]*([0-9]{1,3})/i
    const rtRwMatch = cleanText.match(rtRwPattern)
    if (rtRwMatch) {
      extractedData.rt = rtRwMatch[1]
      extractedData.rw = rtRwMatch[2]
    }

    // Kelurahan/Desa
    const kelurahanPattern = /(?:KEL\/DESA|KELURAHAN|DESA)[\s:]*([A-Z\s]{2,30})/i
    const kelurahanMatch = cleanText.match(kelurahanPattern)
    if (kelurahanMatch) {
      extractedData.kelurahan = this.capitalizeWords(kelurahanMatch[1].trim())
    }

    // Kecamatan
    const kecamatanPattern = /(?:KECAMATAN)[\s:]*([A-Z\s]{2,30})/i
    const kecamatanMatch = cleanText.match(kecamatanPattern)
    if (kecamatanMatch) {
      extractedData.kecamatan = this.capitalizeWords(kecamatanMatch[1].trim())
    }

    // Religion
    const religionPattern = /(?:AGAMA)[\s:]*([A-Z\s]{2,15})/i
    const religionMatch = cleanText.match(religionPattern)
    if (religionMatch) {
      extractedData.religion = this.capitalizeWords(religionMatch[1].trim())
    }

    // Marital Status
    const maritalPattern = /(?:STATUS.*?PERKAWINAN|KAWIN)[\s:]*([A-Z\s]{2,20})/i
    const maritalMatch = cleanText.match(maritalPattern)
    if (maritalMatch) {
      extractedData.maritalStatus = this.capitalizeWords(maritalMatch[1].trim())
    }

    // Occupation
    const occupationPattern = /(?:PEKERJAAN)[\s:]*([A-Z\s]{2,30})/i
    const occupationMatch = cleanText.match(occupationPattern)
    if (occupationMatch) {
      extractedData.occupation = this.capitalizeWords(occupationMatch[1].trim())
    }

    // Nationality
    const nationalityPattern = /(?:KEWARGANEGARAAN|WNI|WNA)[\s:]*([A-Z\s]{2,20})/i
    const nationalityMatch = cleanText.match(nationalityPattern)
    if (nationalityMatch) {
      extractedData.nationality = nationalityMatch[1].includes('WNI') ? 'WNI' : this.capitalizeWords(nationalityMatch[1].trim())
    }

    // Province
    const provincePattern = /(?:PROVINSI)[\s:]*([A-Z\s]{2,30})/i
    const provinceMatch = cleanText.match(provincePattern)
    if (provinceMatch) {
      extractedData.province = this.capitalizeWords(provinceMatch[1].trim())
    }

    return extractedData
  }

  /**
   * Extract transaction data (existing functionality)
   */
  private extractTransactionData(text: string): OCRResult['extractedData'] {
    const extractedData: OCRResult['extractedData'] = {}
    const cleanText = text.replace(/\s+/g, ' ').trim()

    // Extract amount (Indonesian Rupiah patterns)
    const amountPatterns = [
      /(?:Rp\.?\s*|IDR\s*)([0-9]{1,3}(?:[.,][0-9]{3})*(?:[.,][0-9]{2})?)/i,
      /(?:nominal|jumlah|total|amount)[\s:]*(?:Rp\.?\s*)?([0-9]{1,3}(?:[.,][0-9]{3})*)/i,
      /([0-9]{1,3}(?:[.,][0-9]{3})+)/g
    ]

    for (const pattern of amountPatterns) {
      const match = cleanText.match(pattern)
      if (match) {
        const amountStr = match[1] || match[0]
        const amount = parseFloat(amountStr.replace(/[.,]/g, '').replace(/[^\d]/g, ''))
        if (amount > 1000) { // Minimum reasonable donation amount
          extractedData.amount = amount
          break
        }
      }
    }

    // Extract date patterns (Indonesian format)
    const datePatterns = [
      /(\d{1,2}[\/\-\.]\d{1,2}[\/\-\.]\d{4})/,
      /(\d{1,2}\s+(?:Jan|Feb|Mar|Apr|Mei|Jun|Jul|Agu|Sep|Okt|Nov|Des)\s+\d{4})/i,
      /(?:tanggal|tgl|date)[\s:]*(\d{1,2}[\/\-\.]\d{1,2}[\/\-\.]\d{4})/i
    ]

    for (const pattern of datePatterns) {
      const match = cleanText.match(pattern)
      if (match) {
        extractedData.date = this.standardizeDate(match[1])
        break
      }
    }

    // Extract reference number patterns
    const refPatterns = [
      /(?:ref|reference|no\.?\s*ref|referensi)[\s:]*([A-Z0-9]{6,20})/i,
      /(?:trx|transaction|transaksi)[\s:]*([A-Z0-9]{6,20})/i,
      /([0-9]{10,20})/g // Long number sequences
    ]

    for (const pattern of refPatterns) {
      const matches = cleanText.match(pattern)
      if (matches) {
        const ref = Array.isArray(matches) ? matches[1] || matches[0] : matches
        if (ref && ref.length >= 6) {
          extractedData.referenceNumber = ref
          break
        }
      }
    }

    // Extract bank name patterns
    const bankPatterns = [
      /\b(BCA|BRI|BNI|Mandiri|CIMB|Danamon|Permata|OCBC|Maybank|BSI|Muamalat)\b/i,
      /(?:bank|PT\.?\s+Bank)[\s]*([A-Za-z\s]{3,20})/i
    ]

    for (const pattern of bankPatterns) {
      const match = cleanText.match(pattern)
      if (match) {
        extractedData.bankName = match[1].trim()
        break
      }
    }

    // Extract account number
    const accountPatterns = [
      /(?:rekening|account|acc)[\s:]*([0-9]{8,16})/i,
      /(?:ke|to)[\s:]*([0-9]{10,16})/i
    ]

    for (const pattern of accountPatterns) {
      const match = cleanText.match(pattern)
      if (match) {
        extractedData.accountNumber = match[1]
        break
      }
    }

    // Extract donor name patterns
    const namePatterns = [
      /(?:dari|from|pengirim|sender)[\s:]*([A-Za-z\s]{2,30})/i,
      /(?:nama|name)[\s:]*([A-Za-z\s]{2,30})/i
    ]

    for (const pattern of namePatterns) {
      const match = cleanText.match(pattern)
      if (match) {
        const name = match[1].trim()
        if (name.length > 2 && !name.match(/^\d/)) {
          extractedData.donorName = name
          break
        }
      }
    }

    return extractedData
  }

  /**
   * Extract general data (combination of identity and transaction)
   */
  private extractGeneralData(text: string): OCRResult['extractedData'] {
    // Try both identity and transaction extraction
    const identityData = this.extractIdentityData(text)
    const transactionData = this.extractTransactionData(text)
    
    // Merge results, with identity data taking precedence for person info
    return {
      ...transactionData,
      ...identityData
    }
  }

  /**
   * Capitalize words properly
   */
  private capitalizeWords(str: string): string {
    return str.toLowerCase().replace(/\b\w/g, l => l.toUpperCase())
  }

  /**
   * Standardize date format to YYYY-MM-DD
   */
  private standardizeDate(dateStr: string): string {
    try {
      // Handle different date formats
      let normalized = dateStr.replace(/[\/\.]/g, '-')
      
      // Convert month names to numbers
      const monthMap: Record<string, string> = {
        'Jan': '01', 'Feb': '02', 'Mar': '03', 'Apr': '04',
        'Mei': '05', 'Jun': '06', 'Jul': '07', 'Agu': '08',
        'Sep': '09', 'Okt': '10', 'Nov': '11', 'Des': '12'
      }

      for (const [monthName, monthNum] of Object.entries(monthMap)) {
        if (normalized.includes(monthName)) {
          normalized = normalized.replace(monthName, monthNum)
          break
        }
      }

      const parts = normalized.split(/[\s\-]+/)
      if (parts.length === 3) {
        const [day, month, year] = parts
        return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`
      }
    } catch (error) {
      console.error('Date standardization error:', error)
    }
    return dateStr
  }

  /**
   * Clean up resources
   */
  public async terminate(): Promise<void> {
    if (this.worker) {
      await this.worker.terminate()
      this.worker = null
    }
  }
}

export default OCRService.getInstance()