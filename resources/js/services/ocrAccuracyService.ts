import { OCRResult, DocumentTemplate } from './ocrService'

export interface OCRValidationRule {
  field: string
  type: 'format' | 'length' | 'pattern' | 'range' | 'custom'
  rule: string | number | RegExp | ((value: any) => boolean)
  message: string
  severity: 'error' | 'warning' | 'info'
}

export interface OCRAccuracyMetrics {
  overallScore: number
  confidenceScore: number
  patternMatchScore: number
  validationScore: number
  completenessScore: number
  consistencyScore: number
  suggestions: string[]
  warnings: string[]
  errors: string[]
}

export interface LearningData {
  document_type: string
  template_name?: string
  input_text: string
  expected_output: any
  actual_output: any
  confidence: number
  validation_result: boolean
  timestamp: string
  user_corrections?: any
}

export class OCRAccuracyService {
  private static instance: OCRAccuracyService
  private validationRules: Map<string, OCRValidationRule[]> = new Map()
  private learningData: LearningData[] = []
  private performanceMetrics: Map<string, any> = new Map()

  private constructor() {
    this.initializeValidationRules()
    this.loadLearningData()
  }

  public static getInstance(): OCRAccuracyService {
    if (!OCRAccuracyService.instance) {
      OCRAccuracyService.instance = new OCRAccuracyService()
    }
    return OCRAccuracyService.instance
  }

  /**
   * Initialize validation rules for different field types
   */
  private initializeValidationRules(): void {
    // Indonesian NIK validation rules
    this.validationRules.set('nik', [
      {
        field: 'nik',
        type: 'length',
        rule: 16,
        message: 'NIK harus terdiri dari 16 digit',
        severity: 'error'
      },
      {
        field: 'nik',
        type: 'pattern',
        rule: /^[0-9]{16}$/,
        message: 'NIK hanya boleh mengandung angka',
        severity: 'error'
      },
      {
        field: 'nik',
        type: 'custom',
        rule: (value: string) => this.validateIndonesianNIK(value),
        message: 'Format NIK tidak sesuai dengan standar Indonesia',
        severity: 'warning'
      }
    ])

    // Phone number validation
    this.validationRules.set('phoneNumber', [
      {
        field: 'phoneNumber',
        type: 'pattern',
        rule: /^(\+62|62|0)[\d\-\s]{8,13}$/,
        message: 'Format nomor telepon tidak valid untuk Indonesia',
        severity: 'warning'
      }
    ])

    // Amount validation
    this.validationRules.set('amount', [
      {
        field: 'amount',
        type: 'range',
        rule: [1000, 100000000], // Min 1k, Max 100M IDR
        message: 'Jumlah tampak tidak realistis untuk transaksi ZIS',
        severity: 'warning'
      },
      {
        field: 'amount',
        type: 'custom',
        rule: (value: number) => value > 0,
        message: 'Jumlah harus positif',
        severity: 'error'
      }
    ])

    // Date validation
    this.validationRules.set('date', [
      {
        field: 'date',
        type: 'custom',
        rule: (value: string) => this.isValidDate(value),
        message: 'Format tanggal tidak valid',
        severity: 'error'
      },
      {
        field: 'date',
        type: 'custom',
        rule: (value: string) => this.isReasonableDate(value),
        message: 'Tanggal tampak tidak realistis (terlalu jauh di masa lalu/depan)',
        severity: 'warning'
      }
    ])

    // Name validation
    this.validationRules.set('name', [
      {
        field: 'name',
        type: 'length',
        rule: [2, 50],
        message: 'Panjang nama harus antara 2 dan 50 karakter',
        severity: 'warning'
      },
      {
        field: 'name',
        type: 'pattern',
        rule: /^[a-zA-Z\s\.\']+$/,
        message: 'Nama mengandung karakter yang tidak valid',
        severity: 'warning'
      }
    ])
  }

  /**
   * Validate and improve OCR results
   */
  public async validateAndImprove(
    result: OCRResult,
    template?: DocumentTemplate,
    userCorrections?: any
  ): Promise<{ improved: OCRResult; metrics: OCRAccuracyMetrics }> {
    const metrics = this.calculateAccuracyMetrics(result, template)
    const improved = this.applyImprovements(result, metrics, template)

    // Store learning data
    if (userCorrections) {
      this.storeLearningData(result, improved, userCorrections, template)
    }

    return { improved, metrics }
  }

  /**
   * Calculate comprehensive accuracy metrics
   */
  private calculateAccuracyMetrics(
    result: OCRResult,
    template?: DocumentTemplate
  ): OCRAccuracyMetrics {
    const metrics: OCRAccuracyMetrics = {
      overallScore: 0,
      confidenceScore: result.confidence,
      patternMatchScore: 0,
      validationScore: 0,
      completenessScore: 0,
      consistencyScore: 0,
      suggestions: [],
      warnings: [],
      errors: []
    }

    // Pattern matching score
    metrics.patternMatchScore = this.calculatePatternMatchScore(result.extractedData)

    // Validation score
    const validationResult = this.validateExtractedData(result.extractedData)
    metrics.validationScore = validationResult.score
    metrics.errors.push(...validationResult.errors)
    metrics.warnings.push(...validationResult.warnings)

    // Completeness score
    metrics.completenessScore = this.calculateCompletenessScore(result.extractedData, template)

    // Consistency score
    metrics.consistencyScore = this.calculateConsistencyScore(result.extractedData)

    // Generate suggestions
    metrics.suggestions = this.generateSuggestions(result, metrics)

    // Calculate overall score
    metrics.overallScore = (
      metrics.confidenceScore * 0.3 +
      metrics.patternMatchScore * 0.25 +
      metrics.validationScore * 0.25 +
      metrics.completenessScore * 0.15 +
      metrics.consistencyScore * 0.05
    )

    return metrics
  }

  /**
   * Apply improvements based on patterns and learning
   */
  private applyImprovements(
    result: OCRResult,
    metrics: OCRAccuracyMetrics,
    template?: DocumentTemplate
  ): OCRResult {
    const improved = JSON.parse(JSON.stringify(result)) // Deep clone

    // Apply format corrections
    if (improved.extractedData.nik) {
      improved.extractedData.nik = this.correctNIKFormat(improved.extractedData.nik)
    }

    if (improved.extractedData.phoneNumber) {
      improved.extractedData.phoneNumber = this.correctPhoneFormat(improved.extractedData.phoneNumber)
    }

    if (improved.extractedData.amount) {
      improved.extractedData.amount = this.correctAmountFormat(improved.extractedData.amount)
    }

    if (improved.extractedData.date) {
      improved.extractedData.date = this.correctDateFormat(improved.extractedData.date)
    }

    if (improved.extractedData.name) {
      improved.extractedData.name = this.correctNameFormat(improved.extractedData.name)
    }

    // Apply learned corrections
    improved.extractedData = this.applyLearnedCorrections(improved.extractedData, template?.name)

    return improved
  }

  /**
   * Validate extracted data against rules
   */
  private validateExtractedData(data: any): { score: number; errors: string[]; warnings: string[] } {
    let score = 100
    const errors: string[] = []
    const warnings: string[] = []

    for (const [field, value] of Object.entries(data)) {
      if (!value) continue

      const rules = this.validationRules.get(field) || []
      for (const rule of rules) {
        const isValid = this.applyValidationRule(value, rule)
        if (!isValid) {
          if (rule.severity === 'error') {
            errors.push(rule.message)
            score -= 15
          } else if (rule.severity === 'warning') {
            warnings.push(rule.message)
            score -= 5
          }
        }
      }
    }

    return { score: Math.max(0, score), errors, warnings }
  }

  /**
   * Apply a single validation rule
   */
  private applyValidationRule(value: any, rule: OCRValidationRule): boolean {
    switch (rule.type) {
      case 'length':
        if (Array.isArray(rule.rule)) {
          const [min, max] = rule.rule as number[]
          return value.length >= min && value.length <= max
        }
        return value.length === rule.rule
      
      case 'pattern':
        return (rule.rule as RegExp).test(value)
      
      case 'range':
        const [min, max] = rule.rule as number[]
        const numValue = parseFloat(value)
        return numValue >= min && numValue <= max
      
      case 'custom':
        return (rule.rule as Function)(value)
      
      default:
        return true
    }
  }

  /**
   * Calculate pattern matching score
   */
  private calculatePatternMatchScore(data: any): number {
    let score = 0
    let totalFields = 0

    const patterns = {
      nik: /^[0-9]{16}$/,
      phoneNumber: /^(\+62|62|0)[\d\-\s]{8,13}$/,
      email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
      amount: /^\d+(\.\d{2})?$/
    }

    for (const [field, value] of Object.entries(data)) {
      if (!value) continue
      totalFields++

      const pattern = patterns[field as keyof typeof patterns]
      if (pattern && pattern.test(value.toString())) {
        score += 100
      } else if (pattern) {
        score += 30 // Partial credit for having the field
      } else {
        score += 50 // No specific pattern, give partial credit
      }
    }

    return totalFields > 0 ? score / totalFields : 0
  }

  /**
   * Calculate completeness score based on template
   */
  private calculateCompletenessScore(data: any, template?: DocumentTemplate): number {
    if (!template) return 75 // Default score when no template

    const requiredFields = template.patterns.required.length
    const optionalFields = template.patterns.optional.length
    const totalFields = requiredFields + optionalFields

    if (totalFields === 0) return 100

    let score = 0
    let presentRequired = 0
    let presentOptional = 0

    // Check required fields
    for (const field of template.patterns.required) {
      if (data[field]) {
        presentRequired++
        score += 100 / totalFields * 0.8 // Required fields worth more
      }
    }

    // Check optional fields
    for (const field of template.patterns.optional) {
      if (data[field]) {
        presentOptional++
        score += 100 / totalFields * 0.2 // Optional fields worth less
      }
    }

    return Math.min(100, score)
  }

  /**
   * Calculate consistency score (cross-field validation)
   */
  private calculateConsistencyScore(data: any): number {
    let score = 100

    // Check date consistency
    if (data.birthDate && data.date) {
      const birthDate = new Date(data.birthDate)
      const transactionDate = new Date(data.date)
      if (transactionDate < birthDate) {
        score -= 20 // Transaction date before birth date
      }
    }

    // Check amount vs bank consistency
    if (data.amount && data.bankName) {
      // Large amounts should have bank reference
      if (data.amount > 10000000 && !data.referenceNumber) {
        score -= 10
      }
    }

    // Check NIK vs location consistency
    if (data.nik && data.address) {
      // NIK first 2 digits should match province in address (simplified check)
      const provinceCode = data.nik.substring(0, 2)
      // This would need a proper province mapping
    }

    return Math.max(0, score)
  }

  /**
   * Generate improvement suggestions
   */
  private generateSuggestions(result: OCRResult, metrics: OCRAccuracyMetrics): string[] {
    const suggestions: string[] = []

    if (metrics.confidenceScore < 80) {
      suggestions.push('Consider rescanning with better lighting or higher resolution')
    }

    if (metrics.patternMatchScore < 70) {
      suggestions.push('Some extracted data may need manual verification')
    }

    if (metrics.completenessScore < 60) {
      suggestions.push('Several expected fields are missing - check if document is complete')
    }

    if (result.extractedData.nik && !this.validateIndonesianNIK(result.extractedData.nik)) {
      suggestions.push('NIK format appears incorrect - please verify manually')
    }

    return suggestions
  }

  /**
   * Store learning data for future improvements
   */
  private storeLearningData(
    original: OCRResult,
    improved: OCRResult,
    userCorrections: any,
    template?: DocumentTemplate
  ): void {
    const learningEntry: LearningData = {
      document_type: template?.type || 'general',
      template_name: template?.name,
      input_text: original.text,
      expected_output: userCorrections,
      actual_output: original.extractedData,
      confidence: original.confidence,
      validation_result: JSON.stringify(userCorrections) === JSON.stringify(original.extractedData),
      timestamp: new Date().toISOString(),
      user_corrections: userCorrections
    }

    this.learningData.push(learningEntry)
    this.updatePerformanceMetrics()

    // Persist to localStorage for now (in production, send to server)
    this.saveLearningData()
  }

  /**
   * Apply learned corrections based on historical data
   */
  private applyLearnedCorrections(data: any, templateName?: string): any {
    const corrected = { ...data }

    // Find similar cases in learning data
    const similarCases = this.learningData.filter(entry => 
      entry.template_name === templateName &&
      entry.confidence < 90 && // Only learn from low-confidence cases that were corrected
      entry.user_corrections
    )

    // Apply common corrections
    for (const case_ of similarCases) {
      for (const [field, correctedValue] of Object.entries(case_.user_corrections)) {
        if (corrected[field] && this.isSimilarValue(corrected[field], case_.actual_output[field])) {
          // Apply learned correction with confidence threshold
          if (this.getConfidenceInCorrection(field, correctedValue) > 0.7) {
            corrected[field] = correctedValue
          }
        }
      }
    }

    return corrected
  }

  /**
   * Correction helper methods
   */
  private correctNIKFormat(nik: string): string {
    return nik.replace(/\D/g, '').substring(0, 16)
  }

  private correctPhoneFormat(phone: string): string {
    let cleaned = phone.replace(/\D/g, '')
    if (cleaned.startsWith('0')) {
      cleaned = '+62' + cleaned.substring(1)
    } else if (cleaned.startsWith('62')) {
      cleaned = '+' + cleaned
    }
    return cleaned
  }

  private correctAmountFormat(amount: any): number {
    if (typeof amount === 'string') {
      return parseFloat(amount.replace(/[^\d.]/g, ''))
    }
    return parseFloat(amount)
  }

  private correctDateFormat(date: string): string {
    // Try to parse and standardize date format
    const parsed = new Date(date.replace(/[\-\.]/g, '/'))
    if (!isNaN(parsed.getTime())) {
      return parsed.toISOString().split('T')[0]
    }
    return date
  }

  private correctNameFormat(name: string): string {
    return name.split(' ')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
      .join(' ')
  }

  /**
   * Validation helper methods
   */
  private validateIndonesianNIK(nik: string): boolean {
    if (!/^[0-9]{16}$/.test(nik)) return false
    
    // Basic validation - check province code (first 2 digits)
    const provinceCode = parseInt(nik.substring(0, 2))
    return provinceCode >= 11 && provinceCode <= 94
  }

  private isValidDate(dateStr: string): boolean {
    const date = new Date(dateStr)
    return !isNaN(date.getTime())
  }

  private isReasonableDate(dateStr: string): boolean {
    const date = new Date(dateStr)
    const now = new Date()
    const hundredYearsAgo = new Date(now.getFullYear() - 100, now.getMonth(), now.getDate())
    const tenYearsFromNow = new Date(now.getFullYear() + 10, now.getMonth(), now.getDate())
    
    return date >= hundredYearsAgo && date <= tenYearsFromNow
  }

  private isSimilarValue(value1: any, value2: any): boolean {
    const str1 = value1.toString().toLowerCase()
    const str2 = value2.toString().toLowerCase()
    
    // Simple similarity check - can be improved with better algorithms
    if (str1 === str2) return true
    
    // Check Levenshtein distance for strings
    if (typeof value1 === 'string' && typeof value2 === 'string') {
      return this.levenshteinDistance(str1, str2) <= 2
    }
    
    return false
  }

  private levenshteinDistance(str1: string, str2: string): number {
    const matrix = []
    
    for (let i = 0; i <= str2.length; i++) {
      matrix[i] = [i]
    }
    
    for (let j = 0; j <= str1.length; j++) {
      matrix[0][j] = j
    }
    
    for (let i = 1; i <= str2.length; i++) {
      for (let j = 1; j <= str1.length; j++) {
        if (str2.charAt(i - 1) === str1.charAt(j - 1)) {
          matrix[i][j] = matrix[i - 1][j - 1]
        } else {
          matrix[i][j] = Math.min(
            matrix[i - 1][j - 1] + 1, // substitution
            matrix[i][j - 1] + 1,     // insertion
            matrix[i - 1][j] + 1      // deletion
          )
        }
      }
    }
    
    return matrix[str2.length][str1.length]
  }

  private getConfidenceInCorrection(field: string, value: any): number {
    const corrections = this.learningData
      .filter(entry => entry.user_corrections?.[field] === value)
      .length
    
    const total = this.learningData
      .filter(entry => entry.actual_output[field])
      .length
    
    return total > 0 ? corrections / total : 0
  }

  private updatePerformanceMetrics(): void {
    // Calculate and store performance metrics
    const totalEntries = this.learningData.length
    const successfulValidations = this.learningData.filter(e => e.validation_result).length
    
    this.performanceMetrics.set('accuracy_rate', totalEntries > 0 ? successfulValidations / totalEntries : 0)
    this.performanceMetrics.set('total_processed', totalEntries)
    this.performanceMetrics.set('improvement_trend', this.calculateImprovementTrend())
  }

  private calculateImprovementTrend(): number {
    const recentEntries = this.learningData.slice(-100) // Last 100 entries
    if (recentEntries.length < 10) return 0
    
    const firstHalf = recentEntries.slice(0, Math.floor(recentEntries.length / 2))
    const secondHalf = recentEntries.slice(Math.floor(recentEntries.length / 2))
    
    const firstHalfAccuracy = firstHalf.filter(e => e.validation_result).length / firstHalf.length
    const secondHalfAccuracy = secondHalf.filter(e => e.validation_result).length / secondHalf.length
    
    return secondHalfAccuracy - firstHalfAccuracy
  }

  private loadLearningData(): void {
    try {
      const stored = localStorage.getItem('ocr_learning_data')
      if (stored) {
        this.learningData = JSON.parse(stored)
        this.updatePerformanceMetrics()
      }
    } catch (error) {
      console.warn('Failed to load learning data:', error)
    }
  }

  private saveLearningData(): void {
    try {
      // Keep only last 1000 entries to prevent localStorage overflow
      const dataToSave = this.learningData.slice(-1000)
      localStorage.setItem('ocr_learning_data', JSON.stringify(dataToSave))
    } catch (error) {
      console.warn('Failed to save learning data:', error)
    }
  }

  /**
   * Get performance metrics
   */
  public getPerformanceMetrics(): any {
    return Object.fromEntries(this.performanceMetrics)
  }

  /**
   * Reset learning data (for testing or privacy)
   */
  public resetLearningData(): void {
    this.learningData = []
    this.performanceMetrics.clear()
    localStorage.removeItem('ocr_learning_data')
  }
}

export default OCRAccuracyService.getInstance()